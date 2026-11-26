<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * #113: a store pays its approved advert (decisions, user, 26 Nov: after the admin approves; wallet or the client's
 * gateways #92; one price per day per zone; no price = cannot pay online).
 *
 *  - quote($adId, $userId): the advert (approved, unpaid), its store (owner or employee of it), the price per day
 *    (advertisement_prices/{zoneId}.prices.{regionId}), the days (duration rounded up) and the region currency;
 *  - checkout(): writes advertisement_checkouts/{id}; by wallet it completes at once, otherwise the gateway runs
 *    (flow "advertisement");
 *  - complete(): ONE Firestore transaction: the store wallet debit (vendors/{storeId} and the owner's users/{id}, as
 *    the panel's wallet does; wallet only), a `wallet` line, advertisements/{id} paymentStatus true + payment, and the
 *    checkout completed. A second return finds it completed and writes nothing.
 *
 * Firestore is reached with the service account (REST), never from the browser.
 */
class AdvertPayment
{
    const FLOW = 'advertisement';
    const MAX_AGE = 1800;
    const ZERO_DECIMAL = ['XAF', 'XOF', 'GNF', 'RWF'];
    const ID = '/^[A-Za-z0-9_-]{6,64}$/';
    const REF = '/^[A-Za-z0-9_-]{1,64}$/';   // ids a document refers to (store, zone, region)
    /** gateway => [settings document, label, currencies it takes] */
    const GATEWAYS = [
        'mycoolpay' => ['mycoolpay_settings', 'MyCoolPay', ['XAF']],
        'mypvit' => ['mypvit_settings', 'MyPVIT', ['XAF']],
        'sebpay_cm' => ['sebpay_cm_settings', 'Sebpay Cameroun', ['XAF']],
        'sebpay_gn' => ['sebpay_gn_settings', 'Sebpay Guinée', ['GNF']],
        'pispi' => ['pispi_settings', 'PI-SPI', ['XOF']],
    ];

    /* ------------------------------------------------------------ REST */

    protected static function projectId()
    {
        return json_decode(file_get_contents(storage_path('app/firebase/credentials.json')), true)['project_id'] ?? '';
    }

    protected static function base()
    {
        return 'https://firestore.googleapis.com/v1/projects/' . self::projectId() . '/databases/(default)/documents';
    }

    protected static function name($path)
    {
        return 'projects/' . self::projectId() . '/databases/(default)/documents/' . $path;
    }

    protected static function http()
    {
        $token = Cache::remember('advert_payment_sa_token', 3000, function () {
            $client = new \Google_Client();
            $client->setAuthConfig(storage_path('app/firebase/credentials.json'));
            $client->addScope('https://www.googleapis.com/auth/datastore');
            $client->refreshTokenWithAssertion();
            $t = $client->getAccessToken()['access_token'] ?? '';
            if ($t === '') {
                throw new \RuntimeException('no service account token');
            }
            return $t;
        });
        return Http::withToken($token)->acceptJson()->timeout(20);
    }

    public static function encode($v)
    {
        if (is_bool($v)) return ['booleanValue' => $v];
        if (is_int($v)) return ['integerValue' => (string)$v];
        if (is_float($v)) return ['doubleValue' => $v];
        if ($v === null) return ['nullValue' => null];
        if ($v instanceof \DateTimeInterface) return ['timestampValue' => $v->format('Y-m-d\TH:i:s.u\Z')];
        if (is_array($v)) {
            if ($v === [] || array_keys($v) === range(0, count($v) - 1)) {
                return ['arrayValue' => ['values' => array_map([self::class, 'encode'], $v)]];
            }
            return ['mapValue' => ['fields' => array_map([self::class, 'encode'], $v)]];
        }
        return ['stringValue' => (string)$v];
    }

    public static function decode(array $v)
    {
        $k = array_key_first($v);
        $x = $v[$k];
        switch ($k) {
            case 'integerValue': return (int)$x;
            case 'doubleValue': return (float)$x;
            case 'booleanValue': return (bool)$x;
            case 'nullValue': return null;
            case 'timestampValue': return new \DateTimeImmutable($x);
            case 'arrayValue': return array_map([self::class, 'decode'], $x['values'] ?? []);
            case 'mapValue': return array_map([self::class, 'decode'], $x['fields'] ?? []);
            default: return $x;
        }
    }

    protected static function fields(array $doc)
    {
        return array_map([self::class, 'decode'], $doc['fields'] ?? []);
    }

    public static function get($path)
    {
        $r = self::http()->get(self::base() . '/' . $path);
        if ($r->status() === 404) {
            return null;
        }
        if (!$r->successful()) {
            throw new \RuntimeException('Firestore read failed (' . $r->status() . ')');
        }
        return self::fields($r->json());
    }

    protected static function patch($path, array $fields)
    {
        $mask = implode('&', array_map(function ($k) { return 'updateMask.fieldPaths=' . rawurlencode($k); }, array_keys($fields)));
        $r = self::http()->patch(self::base() . '/' . $path . '?' . $mask . '&currentDocument.exists=true',
            ['fields' => array_map([self::class, 'encode'], $fields)]);
        if (!$r->successful()) {
            throw new \RuntimeException('Firestore write failed (' . $r->status() . ')');
        }
    }

    public static function decimals($cur)
    {
        return in_array($cur, self::ZERO_DECIMAL, true) ? 0 : 2;
    }

    protected static function amount($v)
    {
        return is_numeric($v) ? (float)$v : 0.0;
    }

    protected static function money($v, $decimals)
    {
        $v = round((float)$v, $decimals);
        return ($decimals === 0 || floor($v) == $v) ? (int)$v : $v;
    }

    /* ------------------------------------------------------------ quote */

    /** Days of an advert: its duration rounded up, at least 1 (a start during the day counts as a day). */
    public static function days($start, $end)
    {
        if (!$start instanceof \DateTimeInterface || !$end instanceof \DateTimeInterface || $end < $start) {
            return 0;
        }
        return max(1, (int)ceil(($end->getTimestamp() - $start->getTimestamp()) / 86400));
    }

    /** May this login pay for this store: its owner, or one of its employees. */
    public static function mayPay(array $store, $storeId, $userId)
    {
        if ((string)($store['author'] ?? '') === (string)$userId) {
            return true;
        }
        $user = self::get('users/' . $userId);
        return $user && ($user['role'] ?? '') === 'employee' && (string)($user['vendorID'] ?? '') === (string)$storeId;
    }

    /** The approved, unpaid advert and what it costs, or a RuntimeException whose message is shown. */
    public static function quote($adId, $userId)
    {
        if (!preg_match(self::ID, (string)$adId) || !preg_match(self::REF, (string)$userId)) {
            throw new \RuntimeException(trans('lang.adv_pay_unknown'));
        }
        $ad = self::get('advertisements/' . $adId);
        $storeId = (string)($ad['vendorId'] ?? '');
        $store = $ad && preg_match(self::REF, $storeId) ? self::get('vendors/' . $storeId) : null;
        if (!$ad || !$store || !self::mayPay($store, $storeId, $userId)) {
            throw new \RuntimeException(trans('lang.adv_pay_unknown'));
        }
        if (($ad['paymentStatus'] ?? null) === true) {
            throw new \RuntimeException(trans('lang.adv_pay_already_paid'));
        }
        if (($ad['status'] ?? '') !== 'approved') {
            throw new \RuntimeException(trans('lang.adv_pay_not_approved'));
        }
        if (($ad['endDate'] ?? null) instanceof \DateTimeInterface && $ad['endDate']->getTimestamp() < time()) {
            throw new \RuntimeException(trans('lang.adv_pay_ended'));
        }
        $days = self::days($ad['startDate'] ?? null, $ad['endDate'] ?? null);
        $zoneId = (string)($store['zoneId'] ?? '');
        $regionId = (string)($store['regionId'] ?? '');
        $prices = preg_match(self::REF, $zoneId) ? self::get('advertisement_prices/' . $zoneId) : null;
        $perDay = $prices['prices'][$regionId] ?? null;
        $region = preg_match(self::REF, $regionId) ? self::get('regions/' . $regionId) : null;
        $currency = $region && !empty($region['currencyId']) ? self::get('currencies/' . $region['currencyId']) : null;
        $code = strtoupper((string)($currency['code'] ?? ''));
        if ((!is_int($perDay) && !is_float($perDay)) || $perDay < 0 || !$days || !preg_match('/^[A-Z]{3}$/', $code)) {
            throw new \RuntimeException(trans('lang.adv_pay_no_price'));
        }
        $d = self::decimals($code);
        $total = round($days * (float)$perDay, $d);
        if ($total <= 0) {
            throw new \RuntimeException(trans('lang.adv_pay_no_price'));
        }
        return ['ad' => $ad, 'adId' => $adId, 'store' => $store, 'storeId' => $storeId, 'ownerId' => (string)($store['author'] ?? ''),
            'zoneId' => $zoneId, 'regionId' => $regionId, 'days' => $days, 'pricePerDay' => round((float)$perDay, $d),
            'total' => $total, 'currency' => $code, 'currencyData' => $currency ?: [],
            'balance' => self::amount($store['wallet_amount'] ?? 0)];
    }

    /** wallet (when the wallet is on) and each gateway on for the store's region that takes its currency. */
    public static function methods(array $q)
    {
        $methods = [];
        $ws = self::get('settings/walletSettings') ?: [];
        if (($ws['isEnabled'] ?? null) === true) {
            $methods['wallet'] = trans('lang.adv_pay_wallet');
        }
        foreach (self::GATEWAYS as $gw => [$doc, $label, $currencies]) {
            if (!in_array($q['currency'], $currencies, true)) {
                continue;
            }
            $s = self::get('settings/' . $doc) ?: [];
            $on = ($s['isEnabled'] ?? null) === true || ($s['enable'] ?? null) === true;
            $regions = (array)($s['regionIds'] ?? []);
            if ($on && (!$regions || in_array($q['regionId'], $regions, true))) {
                $methods[$gw] = $label;
            }
        }
        return $methods;
    }

    /* ------------------------------------------------------------ checkout */

    /** Opens a checkout: ['checkoutId' => id, 'redirect' => url|null] (null = wallet, to complete at once). */
    public static function checkout($adId, $userId, $method, array $customer = [])
    {
        $q = self::quote($adId, $userId);
        if (!isset(self::methods($q)[$method])) {
            throw new \RuntimeException(trans('lang.adv_pay_method_unavailable'));
        }
        $id = 'adc_' . (int)floor(microtime(true) * 1000) . '_' . bin2hex(random_bytes(5));
        $doc = [
            'id' => $id, 'advertisementId' => $adId, 'storeId' => $q['storeId'], 'ownerId' => $q['ownerId'], 'paidBy' => (string)$userId,
            'method' => $method, 'amount' => $q['total'], 'currency' => $q['currency'], 'days' => $q['days'],
            'pricePerDay' => $q['pricePerDay'], 'zoneId' => $q['zoneId'], 'regionId' => $q['regionId'],
            'status' => $method === 'wallet' ? 'created' : 'paying', 'source' => 'store_panel',
            'createdAt' => new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
        ];
        $r = self::http()->post(self::base() . '/advertisement_checkouts?documentId=' . $id, ['fields' => array_map([self::class, 'encode'], $doc)]);
        if (!$r->successful()) {
            throw new \RuntimeException(trans('lang.adv_pay_failed'));
        }
        if ($method === 'wallet') {
            return ['checkoutId' => $id, 'redirect' => null];
        }
        $ok = route('advertisement-pay.done', $id);
        $ko = route('advertisement-pay.failed', $id);
        $reason = 'Spideli advert ' . $q['days'] . ' days';
        switch ($method) {
            case 'mycoolpay': $url = MyCoolPay::start(self::FLOW, $q['total'], $q['currency'], $reason, $customer, $ok, $ko); break;
            case 'mypvit': $url = MyPVit::start(self::FLOW, $q['total'], $q['currency'], $reason, $customer, $ok, $ko); break;
            case 'sebpay_cm': $url = Sebpay::start('cm', self::FLOW, $q['total'], $q['currency'], $reason, $customer, $ok, $ko); break;
            case 'sebpay_gn': $url = Sebpay::start('gn', self::FLOW, $q['total'], $q['currency'], $reason, $customer, $ok, $ko); break;
            case 'pispi': $url = PiSpi::start(self::FLOW, $q['total'], $q['currency'], $reason, $customer, $ok, $ko); break;
            default: throw new \RuntimeException(trans('lang.adv_pay_method_unavailable'));
        }
        return ['checkoutId' => $id, 'redirect' => $url];
    }

    /** The checkout, for the login that opened it, or a RuntimeException. */
    public static function loadCheckout($id, $userId)
    {
        if (!preg_match('/^adc_[0-9]+_[0-9a-f]{10}$/', (string)$id)) {
            throw new \RuntimeException(trans('lang.adv_pay_unknown'));
        }
        $c = self::get('advertisement_checkouts/' . $id);
        if (!$c || (string)($c['paidBy'] ?? '') !== (string)$userId) {
            throw new \RuntimeException(trans('lang.adv_pay_unknown'));
        }
        return $c;
    }

    /** The gateway's proof (its success-page token), or false. */
    public static function proof($gateway, array $query)
    {
        switch ($gateway) {
            case 'mycoolpay': $p = MyCoolPay::confirmed($query['mycoolpay_token'] ?? null, self::FLOW); break;
            case 'mypvit': $p = MyPVit::confirmed($query['mypvit_token'] ?? null, self::FLOW); break;
            case 'sebpay_cm': case 'sebpay_gn': $p = Sebpay::confirmed($query['sebpay_token'] ?? null, self::FLOW); break;
            case 'pispi': $p = PiSpi::confirmed($query['pispi_token'] ?? null, self::FLOW); break;
            default: $p = false;
        }
        if (!is_array($p)) {
            return false;
        }
        $p['reference'] = (string)($p['reference'] ?? $p['transactionRef'] ?? '');
        return $p;
    }

    /* ------------------------------------------------------------ complete */

    /** Marks the advert paid, once, in one Firestore transaction. $paid = the gateway's payment (null for wallet). */
    public static function complete($id, $userId, array $paid = null)
    {
        $c = self::loadCheckout($id, $userId);
        if (($c['status'] ?? '') === 'completed') {
            return $c;
        }
        $cur = (string)$c['currency'];
        $d = self::decimals($cur);
        $amount = round((float)$c['amount'], $d);
        $byWallet = ($c['method'] ?? '') === 'wallet';
        if (!$byWallet && (!$paid || strtoupper((string)($paid['currency'] ?? '')) !== $cur || abs((float)($paid['amount'] ?? -1) - $amount) > 0.001)) {
            Log::warning('Advert payment: the payment does not match the checkout', ['checkout' => $id]);
            throw new \RuntimeException(trans('lang.mycoolpay_mismatch'));
        }
        if ($byWallet && time() - (($c['createdAt'] ?? null) instanceof \DateTimeInterface ? $c['createdAt']->getTimestamp() : 0) > self::MAX_AGE) {
            throw new \RuntimeException(trans('lang.adv_pay_failed'));
        }
        $http = self::http();
        $adPath = 'advertisements/' . $c['advertisementId'];
        $storePath = 'vendors/' . $c['storeId'];
        $ownerPath = 'users/' . $c['ownerId'];
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $tx = $http->post(self::base() . ':beginTransaction', ['options' => ['readWrite' => new \stdClass()]])->json('transaction');
            if (!$tx) {
                throw new \RuntimeException(trans('lang.adv_pay_failed'));
            }
            $read = $http->post(self::base() . ':batchGet', ['transaction' => $tx, 'documents' => [
                self::name('advertisement_checkouts/' . $id), self::name($adPath), self::name($storePath), self::name($ownerPath),
            ]])->json();
            $docs = [];
            foreach ((array)$read as $row) {
                if (isset($row['found'])) {
                    $docs[substr($row['found']['name'], strlen(self::name('')))] = self::fields($row['found']);
                }
            }
            $now = $docs['advertisement_checkouts/' . $id] ?? null;
            $ad = $docs[$adPath] ?? null;
            $store = $docs[$storePath] ?? null;
            if (!$now || !$ad || !$store) {
                throw new \RuntimeException(trans('lang.adv_pay_unknown'));
            }
            if (($now['status'] ?? '') === 'completed') {
                $http->post(self::base() . ':rollback', ['transaction' => $tx]);
                return $now;
            }
            if (($ad['paymentStatus'] ?? null) === true) {
                $http->post(self::base() . ':rollback', ['transaction' => $tx]);
                throw new \RuntimeException(trans('lang.adv_pay_already_paid'));   // paid meanwhile (by hand or another tab)
            }
            $writes = [];
            if ($byWallet) {
                if (($ad['status'] ?? '') !== 'approved') {   // cancelled meanwhile: take nothing
                    $http->post(self::base() . ':rollback', ['transaction' => $tx]);
                    throw new \RuntimeException(trans('lang.adv_pay_not_approved'));
                }
                $balance = self::amount($store['wallet_amount'] ?? 0);
                if ($balance + 0.0001 < $amount) {
                    $http->post(self::base() . ':rollback', ['transaction' => $tx]);
                    throw new \RuntimeException(trans('lang.adv_pay_insufficient'));
                }
                $writes[] = self::setField($storePath, 'wallet_amount', self::money($balance - $amount, 2));
                if (isset($docs[$ownerPath])) {
                    $writes[] = self::setField($ownerPath, 'wallet_amount', self::money(self::amount($docs[$ownerPath]['wallet_amount'] ?? 0) - $amount, 2));
                }
                $writes[] = ['update' => ['name' => self::name('wallet/' . $id), 'fields' => array_map([self::class, 'encode'], [
                    'id' => $id, 'amount' => self::money($amount, $d), 'isTopUp' => false, 'advertisementId' => (string)$c['advertisementId'],
                    'payment_method' => 'Wallet', 'payment_status' => 'success', 'user_id' => (string)$c['ownerId'],
                    'vendorID' => (string)$c['storeId'], 'transactionUser' => 'vendor', 'note' => 'advertisement amount debited',
                ])], 'currentDocument' => ['exists' => false], 'updateTransforms' => [['fieldPath' => 'date', 'setToServerValue' => 'REQUEST_TIME']]];
            }
            $method = (string)$c['method'];
            $payment = ['amount' => self::money($amount, $d), 'currency' => $cur, 'days' => (int)$c['days'],
                'pricePerDay' => self::money((float)$c['pricePerDay'], $d), 'zoneId' => (string)$c['zoneId'], 'regionId' => (string)$c['regionId'],
                'method' => $method, 'methodLabel' => $byWallet ? 'Wallet' : (self::GATEWAYS[$method][1] ?? $method),
                'reference' => (string)($paid['reference'] ?? ''), 'checkoutId' => $id, 'paidAt' => new \DateTimeImmutable('now', new \DateTimeZone('UTC'))];
            $writes[] = ['update' => ['name' => self::name($adPath), 'fields' => array_map([self::class, 'encode'], ['paymentStatus' => true, 'payment' => $payment])],
                'updateMask' => ['fieldPaths' => ['paymentStatus', 'payment']], 'currentDocument' => ['exists' => true]];
            $writes[] = ['update' => ['name' => self::name('advertisement_checkouts/' . $id), 'fields' => array_map([self::class, 'encode'], [
                'status' => 'completed', 'paymentReference' => (string)($paid['reference'] ?? ''),
            ])], 'updateMask' => ['fieldPaths' => ['status', 'paymentReference']], 'currentDocument' => ['exists' => true],
                'updateTransforms' => [['fieldPath' => 'completedAt', 'setToServerValue' => 'REQUEST_TIME']]];
            $r = $http->post(self::base() . ':commit', ['transaction' => $tx, 'writes' => $writes]);
            if ($r->successful()) {
                return array_merge($now, ['status' => 'completed']);
            }
            if ($r->status() !== 409 && $r->status() !== 400) {
                break;
            }
        }
        Log::error('Advert payment could not be completed', ['checkout' => $id]);
        throw new \RuntimeException(trans('lang.adv_pay_failed'));
    }

    protected static function setField($path, $field, $value)
    {
        return ['update' => ['name' => self::name($path), 'fields' => [$field => self::encode($value)]],
            'updateMask' => ['fieldPaths' => [$field]], 'currentDocument' => ['exists' => true]];
    }

    /** Marks an unfinished checkout failed, or paid_not_credited (paid at the gateway, advert not marked paid). */
    public static function failCheckout($id, $userId, $why, $status = 'failed', $reference = '')
    {
        try {
            $c = self::loadCheckout($id, $userId);
            if (!in_array($c['status'] ?? '', ['completed', 'paid_not_credited'], true)) {
                $f = ['status' => $status, 'failureReason' => mb_substr((string)$why, 0, 200)];
                if ((string)$reference !== '') {
                    $f['paymentReference'] = mb_substr((string)$reference, 0, 100);
                }
                self::patch('advertisement_checkouts/' . $id, $f);
            }
        } catch (\Throwable $e) {
            Log::warning('Advert checkout not marked failed: ' . $e->getMessage());
        }
    }
}
