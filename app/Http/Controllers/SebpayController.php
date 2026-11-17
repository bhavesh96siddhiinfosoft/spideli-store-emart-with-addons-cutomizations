<?php

namespace App\Http\Controllers;

use App\Services\Sebpay;
use Illuminate\Support\Facades\Session;

/**
 * #92: Sebpay sends the customer back here (/sebpay/{cm|gn}/return, given with
 * the payment). The session is asked to Sebpay server-to-server and accepted
 * only when "approved" for the same reference, amount and currency.
 */
class SebpayController extends Controller
{
    const MAX_WAIT_CHECKS = 24;   // about 2 minutes at one check every 5 seconds

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function back($country)
    {
        $p = Session::get(Sebpay::SESSION);
        if (!$p || ($p['country'] ?? '') !== $country) {
            return redirect('/')->with('mycoolpay_error', trans('lang.sebpay_no_pending'));
        }
        $session = Sebpay::retrieve($country, $p['sessionId']);
        if (Sebpay::proves($session, $p)) {
            $p['confirmed'] = true;
            Session::put(Sebpay::SESSION, $p);
            Session::save();
            $url = $p['successUrl'];
            return redirect($url . (strpos($url, '?') === false ? '?' : '&') . 'sebpay_token=' . $p['confirmToken']);
        }
        $tx = strtolower((string)($session['transaction_status'] ?? ''));
        $st = strtolower((string)($session['status'] ?? ''));
        $pending = !$session || ($tx === '' || $tx === 'pending') && !in_array($st, ['expired', 'canceled'], true);
        if ($pending) {
            $p['checks'] = (int)($p['checks'] ?? 0) + 1;
            Session::put(Sebpay::SESSION, $p);
            Session::save();
            if ($p['checks'] <= self::MAX_WAIT_CHECKS) {
                return response()->view('payment.waiting', ['retryUrl' => route('sebpay.return', $country), 'text' => trans('lang.sebpay_waiting_text')]);
            }
        }
        $message = $tx === 'approved' ? trans('lang.mycoolpay_mismatch') : ($pending ? trans('lang.mypvit_still_pending') : trans('lang.mycoolpay_not_paid'));
        Session::forget(Sebpay::SESSION);
        Session::save();
        return redirect($p['failUrl'] ?: '/')->with('mycoolpay_error', $message);
    }
}
