<?php

namespace App\Http\Controllers;

use App\Models\VendorUsers;
use App\Services\AdvertPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * #113: the store pays its approved advert (App\Services\AdvertPayment).
 *  - GET  /advertisements/pay/{id}                    the price and the payment methods;
 *  - POST /advertisements/pay/{id}                    by wallet at once, otherwise the gateway;
 *  - GET  /advertisements/pay/checkout/{id}/done      the gateway's success page;
 *  - GET  /advertisements/pay/checkout/{id}/failed    the gateway's failure page.
 * Only the store's owner or one of its employees can pay; a checkout is read back for the login that opened it.
 */
class AdvertPaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show($id)
    {
        try {
            $q = AdvertPayment::quote($id, $this->userId());
            $options = AdvertPayment::options($q);   // every method of the region, usable or not yet
        } catch (\Throwable $e) {
            return $this->result(false, $this->message($e), $id);
        }
        $methods = array_map(function ($o) { return $o['label']; }, array_filter($options, function ($o) { return $o['usable']; }));
        return view('advertisements.pay', ['q' => $q, 'options' => $options, 'methods' => $methods, 'error' => session('adv_pay_error')]);
    }

    public function pay(Request $request, $id)
    {
        $userId = $this->userId();
        $customer = ['name' => Auth::user()->name ?? '', 'email' => Auth::user()->email ?? ''];
        try {
            $c = AdvertPayment::checkout($id, $userId, (string)$request->input('method', ''), $customer);
        } catch (\Throwable $e) {
            return redirect()->route('advertisement-pay.show', $id)->with('adv_pay_error', $this->message($e));
        }
        if ($c['redirect'] !== null) {
            return redirect()->away($c['redirect']);
        }
        try {
            AdvertPayment::complete($c['checkoutId'], $userId);
        } catch (\Throwable $e) {
            AdvertPayment::failCheckout($c['checkoutId'], $userId, $e->getMessage());
            return redirect()->route('advertisement-pay.show', $id)->with('adv_pay_error', $this->message($e));
        }
        return $this->result(true, trans('lang.adv_pay_done'), $id);
    }

    public function done(Request $request, $id)
    {
        $userId = $this->userId();
        try {
            $c = AdvertPayment::loadCheckout($id, $userId);
        } catch (\Throwable $e) {
            return $this->result(false, trans('lang.adv_pay_unknown'), null);
        }
        $adId = (string)($c['advertisementId'] ?? '');
        if (($c['status'] ?? '') === 'completed') {
            return $this->result(true, trans('lang.adv_pay_done'), $adId);
        }
        $paid = AdvertPayment::proof((string)($c['method'] ?? ''), $request->query(), $c + ['id' => $id]);
        if (!$paid) {
            return $this->result(false, trans('lang.adv_pay_not_confirmed'), $adId);
        }
        try {
            AdvertPayment::complete($id, $userId, $paid);
        } catch (\Throwable $e) {
            // Paid at the gateway but the advert could not be marked paid: kept for the admin.
            Log::error('Advert paid but not marked paid', ['checkout' => $id, 'error' => $e->getMessage()]);
            AdvertPayment::failCheckout($id, $userId, $e->getMessage(), 'paid_not_credited', (string)($paid['reference'] ?? ''));
            return $this->result(false, trans('lang.adv_pay_paid_not_done'), $adId);
        }
        return $this->result(true, trans('lang.adv_pay_done'), $adId);
    }

    /** Razorpay: the page that opens Razorpay Checkout for the checkout's order (key id only; the secret stays here). */
    public function razorpay($id)
    {
        $userId = $this->userId();
        try {
            $c = AdvertPayment::loadCheckout($id, $userId);
        } catch (\Throwable $e) {
            return $this->result(false, trans('lang.adv_pay_unknown'), null);
        }
        $adId = (string)($c['advertisementId'] ?? '');
        if (($c['status'] ?? '') === 'completed') {
            return $this->result(true, trans('lang.adv_pay_done'), $adId);
        }
        $s = AdvertPayment::settings('razorpay');
        if (($c['method'] ?? '') !== 'razorpay' || ($c['status'] ?? '') !== 'paying' || empty($c['gatewayOrderId'])
            || !\App\Services\RazorpayCheckout::ready($s)) {
            return $this->result(false, trans('lang.adv_pay_failed'), $adId);
        }
        return view('advertisements.pay_razorpay', [
            'checkout' => $c, 'checkoutId' => $id, 'adId' => $adId,
            'key' => trim((string)$s['razorpayKey']),
            'amountMinor' => \App\Services\RazorpayCheckout::minor($c['amount'], $c['currency']),
            'customer' => ['name' => Auth::user()->name ?? '', 'email' => Auth::user()->email ?? ''],
        ]);
    }

    public function failed($id)
    {
        $userId = $this->userId();
        $why = (string)session('mycoolpay_error', trans('lang.mycoolpay_not_paid'));
        AdvertPayment::failCheckout($id, $userId, $why);
        $adId = null;
        try {
            $adId = (string)(AdvertPayment::loadCheckout($id, $userId)['advertisementId'] ?? '');
        } catch (\Throwable $e) {
        }
        return $this->result(false, $why, $adId);
    }

    protected function userId()
    {
        $u = VendorUsers::where('user_id', Auth::id())->first();
        abort_if(!$u || !$u->uuid, 403);
        return $u->uuid;
    }

    protected function message(\Throwable $e)
    {
        if (!$e instanceof \RuntimeException) {
            Log::warning('Advert payment: ' . $e->getMessage());
            return trans('lang.adv_pay_failed');
        }
        return $e->getMessage();
    }

    protected function result($ok, $message, $adId)
    {
        return view('advertisements.pay_result', ['ok' => (bool)$ok, 'message' => $message, 'adId' => $adId]);
    }
}
