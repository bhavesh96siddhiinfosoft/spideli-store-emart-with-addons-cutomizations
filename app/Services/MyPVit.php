<?php

namespace App\Services;

use App\Helpers\FirestoreHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * #92: MyPVIT Gabon (PVit) for this panel's subscription payments. The same
 * PVit calls as the customer website's App\Services\MyPVit, with two
 * differences, because PVit's return links and callback are on the website:
 *  - the customer goes to PVit through the website's /mypvit/handoff, which
 *    remembers this panel's return page and sends the customer back here;
 *  - the payment's confirmed status is asked to the website
 *    (/mypvit/status/{reference}), which receives PVit's callback and checks it
 *    with PVit's status API.
 * Settings: settings/mypvit_settings. The secret key is renewed here and cached;
 * it never reaches a browser.
 */
class MyPVit
{
    const API = 'https://api.mypvit.pro/';
    const SESSION = 'mypvit_payment';
    const PANEL = 'store';

    public static function settings()
    {
        try {
            return FirestoreHelper::getDocument('settings/mypvit_settings');
        } catch (\Throwable $e) {
            Log::warning('MyPVIT settings could not be read: ' . $e->getMessage());
            return null;
        }
    }

    public static function enabled(array $s = null)
    {
        $s = $s ?? self::settings();
        if (!$s || !(($s['isEnabled'] ?? null) === true || ($s['enable'] ?? null) === true)) {
            return false;
        }
        foreach (['accountCode', 'password', 'secretCode', 'linkCode', 'callbackCode', 'successRedirectCode', 'failedRedirectCode', 'successUrl'] as $k) {
            if (trim((string)($s[$k] ?? '')) === '') {
                return false;
            }
        }
        return true;
    }

    protected static function code($v)
    {
        $v = trim((string)$v, " /\t\n");
        return preg_match('/^[A-Za-z0-9]{4,60}$/', $v) ? $v : '';
    }

    /** The website's address, from the success link the admin registered in PVit. */
    public static function websiteBase(array $s)
    {
        return preg_replace('#/mypvit/return/success/?$#', '', (string)($s['successUrl'] ?? ''));
    }

    public static function secret(array $s, $fresh = false)
    {
        $key = 'mypvit_secret_' . md5((string)$s['accountCode'] . '|' . (string)$s['secretCode']);
        if (!$fresh && ($cached = Cache::get($key))) {
            return $cached;
        }
        $code = self::code($s['secretCode'] ?? '');
        if ($code === '') {
            throw new \RuntimeException('MyPVIT: invalid Renew Secret URL code');
        }
        $r = Http::asForm()->acceptJson()->timeout(20)->post(self::API . 'v2/' . $code . '/renew-secret', [
            'operationAccountCode' => (string)$s['accountCode'], 'password' => (string)$s['password'],
        ]);
        $secret = (string)($r->json('secret') ?? '');
        if (!$r->successful() || $secret === '') {
            Log::warning('MyPVIT renew-secret refused', ['http' => $r->status(), 'message' => $r->json('message')]);
            throw new \RuntimeException(trans('lang.mypvit_start_failed'));
        }
        Cache::put($key, $secret, max(60, (int)($r->json('expires_in') ?? 3600) - 300));
        return $secret;
    }

    /** Starts a payment; returns the URL to send the customer to (the website's hand-off, then PVit). */
    public static function start($flow, $amount, $currency, $reason, array $customer, $successUrl, $failUrl)
    {
        $s = self::settings();
        if (!self::enabled($s)) {
            throw new \RuntimeException(trans('lang.mypvit_unavailable'));
        }
        if (strtoupper((string)$currency) !== 'XAF') {
            throw new \RuntimeException(trans('lang.mycoolpay_wrong_currency', ['currency' => strtoupper((string)$currency)]));
        }
        $amount = (int)round((float)$amount);
        if ($amount <= 0) {
            throw new \RuntimeException(trans('lang.mycoolpay_wrong_amount'));
        }
        $reference = 'SP' . strtoupper(substr(self::PANEL, 0, 1)) . strtoupper(bin2hex(random_bytes(6)));   // 15 characters
        $body = array_filter([
            'amount' => $amount, 'reference' => $reference, 'service' => 'WEB',
            'callback_url_code' => (string)$s['callbackCode'], 'merchant_operation_account_code' => (string)$s['accountCode'],
            'transaction_type' => 'PAYMENT',
            'owner_charge' => in_array($s['ownerCharge'] ?? '', ['CUSTOMER', 'MERCHANT'], true) ? $s['ownerCharge'] : 'CUSTOMER',
            'owner_charge_operator' => in_array($s['operatorOwnerCharge'] ?? '', ['CUSTOMER', 'MERCHANT'], true) ? $s['operatorOwnerCharge'] : null,
            'success_redirection_url_code' => (string)$s['successRedirectCode'], 'failed_redirection_url_code' => (string)$s['failedRedirectCode'],
            'customer_account_number' => preg_replace('/[^0-9]/', '', (string)($customer['phone'] ?? '')) ?: null,
            'free_info' => mb_substr((string)$reason, 0, 50), 'product' => self::PANEL . '_' . $flow,
        ], function ($v) { return $v !== null && $v !== ''; });
        $send = function ($secret) use ($s, $body) {
            return Http::acceptJson()->timeout(25)->withHeaders(['X-Secret' => $secret, 'X-Callback-MediaType' => 'application/json'])
                ->post(self::API . self::code($s['linkCode']) . '/link', $body);
        };
        $r = $send(self::secret($s));
        if ($r->status() === 401 || $r->status() === 403) {
            $r = $send(self::secret($s, true));
        }
        $url = (string)($r->json('url') ?? '');
        if (!$r->successful() || $url === '' || !preg_match('#^https://#', $url)) {
            Log::warning('MyPVIT link refused', ['http' => $r->status(), 'message' => $r->json('message'), 'flow' => $flow]);
            throw new \RuntimeException(trans('lang.mypvit_start_failed'));
        }
        Session::put(self::SESSION, [
            'flow' => $flow, 'reference' => $reference, 'amount' => $amount, 'currency' => 'XAF',
            'successUrl' => $successUrl, 'failUrl' => $failUrl, 'website' => self::websiteBase($s),
            'confirmToken' => bin2hex(random_bytes(16)), 'confirmed' => false, 'startedAt' => time(),
        ]);
        Session::save();
        return self::websiteBase($s) . '/mypvit/handoff?to=' . rawurlencode($url) . '&back=' . rawurlencode(rtrim(url('/mypvit/return'), '/'));
    }

    /** The website's confirmed status for this panel's pending payment, or null. */
    public static function remoteStatus(array $pending)
    {
        try {
            $r = Http::acceptJson()->timeout(30)->get(rtrim((string)$pending['website'], '/') . '/mypvit/status/' . $pending['reference']);
            return $r->successful() ? $r->json() : null;
        } catch (\Throwable $e) {
            Log::warning('MyPVIT status from the website failed: ' . $e->getMessage());
            return null;
        }
    }

    public static function proves(array $status = null, array $pending = null)
    {
        return $status && $pending && !empty($status['verified'])
            && strtoupper((string)($status['status'] ?? '')) === 'SUCCESS'
            && (string)($status['reference'] ?? '') === (string)$pending['reference']
            && abs((float)($status['amount'] ?? -1) - (float)$pending['amount']) < 0.01;
    }

    public static function confirmed($token, $flow)
    {
        $p = Session::get(self::SESSION);
        if (!$p || empty($p['confirmed']) || ($p['flow'] ?? '') !== $flow || !is_string($token) || !hash_equals((string)$p['confirmToken'], $token)) {
            return false;
        }
        Session::forget(self::SESSION);
        Session::save();
        return $p;
    }
}
