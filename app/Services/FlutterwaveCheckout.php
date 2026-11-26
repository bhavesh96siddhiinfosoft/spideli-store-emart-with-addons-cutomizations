<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * #113: FlutterWave for the advert payment, run on the server (Standard checkout, API v3).
 *
 *  - start(): POST /v3/payments with our checkout id as `tx_ref` → the hosted payment link;
 *  - verify(): GET /v3/transactions/verify_by_reference?tx_ref=… → the payment only when FlutterWave says
 *    `successful` for OUR reference. The return URL's own `status` / `transaction_id` are never trusted.
 *
 * Keys: settings/flutterWave `secretKey` (server only), `publicKey`; `isEnable`; "Available in Regions" `regionIds`.
 * Test keys (isSandbox) use the same API.
 */
class FlutterwaveCheckout
{
    const API = 'https://api.flutterwave.com/v3';

    /** Switched on with its keys filled. */
    public static function ready(array $s)
    {
        $on = ($s['isEnable'] ?? null) === true || ($s['isEnabled'] ?? null) === true || ($s['enable'] ?? null) === true;
        return $on && trim((string)($s['secretKey'] ?? '')) !== '' && trim((string)($s['publicKey'] ?? '')) !== '';
    }

    protected static function http(array $s)
    {
        return Http::withToken(trim((string)$s['secretKey']))->acceptJson()->timeout(25);
    }

    /** The hosted payment link, or a RuntimeException. */
    public static function start(array $s, $txRef, $amount, $currency, $title, array $customer, $redirectUrl)
    {
        $r = self::http($s)->post(self::API . '/payments', [
            'tx_ref' => (string)$txRef,
            'amount' => $amount,
            'currency' => (string)$currency,
            'redirect_url' => (string)$redirectUrl,
            'customer' => [
                'email' => (string)($customer['email'] ?? ''),
                'name' => (string)($customer['name'] ?? ''),
            ],
            'customizations' => ['title' => mb_substr((string)$title, 0, 100)],
        ]);
        $link = $r->json('data.link');
        if (!$r->successful() || ($r->json('status') ?? '') !== 'success' || !is_string($link) || stripos($link, 'https://') !== 0) {
            Log::warning('FlutterWave: payment link refused', ['status' => $r->status(), 'message' => mb_substr((string)$r->json('message'), 0, 200)]);
            throw new \RuntimeException(trans('lang.adv_pay_gateway_refused'));
        }
        return $link;
    }

    /** ['amount', 'currency', 'reference'] when FlutterWave confirms our tx_ref was paid, else false. */
    public static function verify(array $s, $txRef)
    {
        try {
            $r = self::http($s)->get(self::API . '/transactions/verify_by_reference', ['tx_ref' => (string)$txRef]);
        } catch (\Throwable $e) {
            Log::warning('FlutterWave: verify failed: ' . $e->getMessage());
            return false;
        }
        $d = (array)$r->json('data');
        if (!$r->successful() || ($r->json('status') ?? '') !== 'success' || ($d['status'] ?? '') !== 'successful'
            || (string)($d['tx_ref'] ?? '') !== (string)$txRef) {
            return false;
        }
        return ['amount' => (float)($d['amount'] ?? -1), 'currency' => strtoupper((string)($d['currency'] ?? '')),
            'reference' => (string)($d['flw_ref'] ?? $d['id'] ?? '')];
    }
}
