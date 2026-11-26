<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * #113: Razorpay for the advert payment, run on the server (Orders API + Standard Checkout).
 *
 *  - order(): POST /v1/orders (amount in the currency's smallest unit, receipt = our checkout id) → order id; the page
 *    opens Razorpay Checkout for that order;
 *  - verify(): the checkout's signature (HMAC-SHA256 of "order_id|payment_id" with the key secret) must match, then
 *    GET /v1/payments/{id}: same order, amount and currency; an `authorized` payment is captured here; only `captured`
 *    counts. Nothing the browser sends is trusted on its own.
 *
 * Keys: settings/razorpaySettings `razorpayKey` (key id, shown to the browser), `razorpaySecret` (server only);
 * `isEnabled`; "Available in Regions" `regionIds`.
 */
class RazorpayCheckout
{
    const API = 'https://api.razorpay.com/v1';
    const ZERO_DECIMAL = ['JPY', 'KRW', 'VND', 'CLP', 'PYG', 'XAF', 'XOF', 'GNF', 'RWF', 'UGX', 'VUV', 'BIF', 'DJF', 'KMF', 'MGA', 'XPF'];

    /** Switched on with both keys filled. */
    public static function ready(array $s)
    {
        $on = ($s['isEnabled'] ?? null) === true || ($s['isEnable'] ?? null) === true || ($s['enable'] ?? null) === true;
        return $on && trim((string)($s['razorpayKey'] ?? '')) !== '' && trim((string)($s['razorpaySecret'] ?? '')) !== '';
    }

    public static function minor($amount, $currency)
    {
        return (int)round((float)$amount * (in_array(strtoupper((string)$currency), self::ZERO_DECIMAL, true) ? 1 : 100));
    }

    protected static function http(array $s)
    {
        return Http::withBasicAuth(trim((string)$s['razorpayKey']), trim((string)$s['razorpaySecret']))->acceptJson()->timeout(25);
    }

    /** A Razorpay order id for this amount, or a RuntimeException. */
    public static function order(array $s, $receipt, $amount, $currency)
    {
        $r = self::http($s)->post(self::API . '/orders', [
            'amount' => self::minor($amount, $currency),
            'currency' => strtoupper((string)$currency),
            'receipt' => mb_substr((string)$receipt, 0, 40),
        ]);
        $id = $r->json('id');
        if (!$r->successful() || !is_string($id) || !preg_match('/^order_[A-Za-z0-9]+$/', $id)) {
            Log::warning('Razorpay: order refused', ['status' => $r->status(), 'error' => mb_substr((string)$r->json('error.description'), 0, 200)]);
            throw new \RuntimeException(trans('lang.adv_pay_gateway_refused'));
        }
        return $id;
    }

    /** ['amount', 'currency', 'reference'] when the payment of this order is genuine and captured, else false. */
    public static function verify(array $s, $orderId, $paymentId, $signature, $amount, $currency)
    {
        $orderId = (string)$orderId;
        $paymentId = (string)$paymentId;
        if (!preg_match('/^order_[A-Za-z0-9]+$/', $orderId) || !preg_match('/^pay_[A-Za-z0-9]+$/', $paymentId) || !is_string($signature) || $signature === '') {
            return false;
        }
        $expected = hash_hmac('sha256', $orderId . '|' . $paymentId, trim((string)$s['razorpaySecret']));
        if (!hash_equals($expected, $signature)) {
            Log::warning('Razorpay: signature mismatch', ['order' => $orderId]);
            return false;
        }
        $minor = self::minor($amount, $currency);
        $cur = strtoupper((string)$currency);
        try {
            $p = self::http($s)->get(self::API . '/payments/' . $paymentId);
            $pay = (array)$p->json();
            if (!$p->successful() || ($pay['order_id'] ?? '') !== $orderId || (int)($pay['amount'] ?? -1) !== $minor || strtoupper((string)($pay['currency'] ?? '')) !== $cur) {
                return false;
            }
            if (($pay['status'] ?? '') === 'authorized') {   // auto-capture off on the account: capture it now
                $c = self::http($s)->post(self::API . '/payments/' . $paymentId . '/capture', ['amount' => $minor, 'currency' => $cur]);
                $pay = (array)$c->json();
            }
            if (($pay['status'] ?? '') !== 'captured') {
                return false;
            }
        } catch (\Throwable $e) {
            Log::warning('Razorpay: verify failed: ' . $e->getMessage());
            return false;
        }
        return ['amount' => (float)$amount, 'currency' => $cur, 'reference' => $paymentId];
    }
}
