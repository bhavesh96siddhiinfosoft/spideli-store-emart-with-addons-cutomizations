<?php

namespace App\Services;

use App\Helpers\FirestoreHelper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * #92: MyCoolPay Cameroun for this panel's subscription payments (the same
 * service as the customer website's App\Services\MyCoolPay, without the
 * callback, which the website receives).
 *
 * The MyCoolPay application has one set of return links, on the website
 * (https://spideli.com/mycoolpay/return/...). This panel adds its own return
 * address to the payment link (spd_back); MyCoolPay hands it back and the
 * website forwards the customer here, to /mycoolpay/return/{result}, where
 * the status is asked to MyCoolPay before the payment is accepted.
 *
 * Settings: settings/mycoolpay_settings (isEnabled, publicKey, currency...).
 * Only the public key is used here. There is no sandbox.
 */
class MyCoolPay
{
    const API = 'https://my-coolpay.com/api/';
    const SESSION = 'mycoolpay_payment';
    const PANEL = 'store';

    public static function settings()
    {
        try {
            return FirestoreHelper::getDocument('settings/mycoolpay_settings');
        } catch (\Throwable $e) {
            Log::warning('MyCoolPay settings could not be read: ' . $e->getMessage());
            return null;
        }
    }

    public static function enabled(array $settings = null)
    {
        $settings = $settings ?? self::settings();
        return $settings && (($settings['isEnabled'] ?? null) === true || ($settings['enable'] ?? null) === true)
            && trim((string)($settings['publicKey'] ?? '')) !== '';
    }

    protected static function api($publicKey)
    {
        return self::API . rawurlencode($publicKey);
    }

    /** Where MyCoolPay (through the website) sends the customer back: this panel's return page. */
    public static function returnBase()
    {
        return rtrim(url('/mycoolpay/return'), '/');
    }

    /** Starts a payment; returns the MyCoolPay payment page URL. */
    public static function start($flow, $amount, $currency, $reason, array $customer, $successUrl, $failUrl)
    {
        $settings = self::settings();
        if (!self::enabled($settings)) {
            throw new \RuntimeException(trans('lang.mycoolpay_unavailable'));
        }
        $currency = strtoupper((string)$currency);
        $allowed = strtoupper((string)($settings['currency'] ?? 'XAF')) === 'EUR' ? ['XAF', 'EUR'] : ['XAF'];
        if (!in_array($currency, $allowed, true)) {
            throw new \RuntimeException(trans('lang.mycoolpay_wrong_currency', ['currency' => $currency]));
        }
        $amount = $currency === 'XAF' ? (int)round((float)$amount) : round((float)$amount, 2);
        if ($amount <= 0) {
            throw new \RuntimeException(trans('lang.mycoolpay_wrong_amount'));
        }
        $appRef = 'SPD-' . strtoupper(substr(self::PANEL, 0, 3)) . '-' . bin2hex(random_bytes(8));
        $lang = in_array(app()->getLocale(), ['fr', 'en'], true) ? app()->getLocale() : 'fr';
        $payload = array_filter([
            'transaction_amount' => $amount,
            'transaction_currency' => $currency,
            'transaction_reason' => mb_substr((string)$reason, 0, 100),
            'app_transaction_ref' => $appRef,
            'customer_phone_number' => preg_replace('/[^0-9]/', '', (string)($customer['phone'] ?? '')) ?: null,
            'customer_name' => mb_substr(trim((string)($customer['name'] ?? '')), 0, 100) ?: null,
            'customer_email' => filter_var($customer['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: null,
            'customer_lang' => $lang,
        ], function ($v) { return $v !== null && $v !== ''; });

        // MyCoolPay copies these parameters onto the return URL (at most 255 characters).
        $query = '?spd_panel=' . self::PANEL . '&spd_back=' . rawurlencode(self::returnBase());
        $r = Http::acceptJson()->timeout(20)->post(self::api($settings['publicKey']) . '/paylink' . $query, $payload);
        $body = $r->json() ?: [];
        if (!$r->successful() || ($body['status'] ?? '') !== 'success' || empty($body['payment_url']) || empty($body['transaction_ref'])) {
            Log::warning('MyCoolPay paylink refused', ['http' => $r->status(), 'message' => $body['message'] ?? null, 'flow' => $flow]);
            throw new \RuntimeException(trans('lang.mycoolpay_start_failed') . (!empty($body['message']) ? ' (' . $body['message'] . ')' : ''));
        }
        Session::put(self::SESSION, [
            'flow' => $flow, 'appRef' => $appRef, 'transactionRef' => $body['transaction_ref'], 'amount' => $amount,
            'currency' => $currency, 'successUrl' => $successUrl, 'failUrl' => $failUrl,
            'confirmToken' => bin2hex(random_bytes(16)), 'confirmed' => false, 'startedAt' => time(),
        ]);
        Session::save();
        return $body['payment_url'];
    }

    public static function checkStatus($transactionRef, $publicKey = null)
    {
        $publicKey = $publicKey ?? (self::settings()['publicKey'] ?? '');
        if ($publicKey === '' || !preg_match('/^[A-Za-z0-9_-]{4,100}$/', (string)$transactionRef)) {
            return null;
        }
        try {
            $r = Http::acceptJson()->timeout(15)->get(self::api($publicKey) . '/checkStatus/' . $transactionRef);
            $body = $r->json();
            return ($r->successful() && ($body['status'] ?? '') === 'success') ? $body : null;
        } catch (\Throwable $e) {
            Log::warning('MyCoolPay checkStatus failed: ' . $e->getMessage());
            return null;
        }
    }

    public static function proves(array $status = null, array $pending = null)
    {
        if (!$status || !$pending) {
            return false;
        }
        return strtoupper((string)($status['transaction_status'] ?? '')) === 'SUCCESS'
            && (string)($status['transaction_ref'] ?? $pending['transactionRef']) === (string)$pending['transactionRef']
            && (!isset($status['app_transaction_ref']) || (string)$status['app_transaction_ref'] === (string)$pending['appRef'])
            && strtoupper((string)($status['transaction_currency'] ?? $pending['currency'])) === strtoupper((string)$pending['currency'])
            && abs((float)($status['transaction_amount'] ?? -1) - (float)$pending['amount']) < 0.01;
    }

    /** Once, for $flow's success page: the confirmed payment, or false. */
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
