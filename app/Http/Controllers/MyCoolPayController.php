<?php

namespace App\Http\Controllers;

use App\Services\MyCoolPay;
use Illuminate\Support\Facades\Session;

/**
 * #92: MyCoolPay brings the customer back here (through the website's return
 * page, which forwards spd_back). The status is asked to MyCoolPay before the
 * subscription's success page may accept the payment.
 */
class MyCoolPayController extends Controller
{
    const MAX_WAIT_CHECKS = 24;   // about 2 minutes at one check every 5 seconds

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function back($result)
    {
        $p = Session::get(MyCoolPay::SESSION);
        if (!$p) {
            return redirect('/')->with('mycoolpay_error', trans('lang.mycoolpay_no_pending'));
        }
        $status = MyCoolPay::checkStatus($p['transactionRef']);
        $state = strtoupper((string)($status['transaction_status'] ?? ''));
        if (MyCoolPay::proves($status, $p)) {
            $p['confirmed'] = true;
            Session::put(MyCoolPay::SESSION, $p);
            Session::save();
            $url = $p['successUrl'];
            return redirect($url . (strpos($url, '?') === false ? '?' : '&') . 'mycoolpay_token=' . $p['confirmToken']);
        }
        if (in_array($state, ['CREATED', 'PENDING', ''], true) && $result === 'success') {
            $p['checks'] = (int)($p['checks'] ?? 0) + 1;
            Session::put(MyCoolPay::SESSION, $p);
            Session::save();
            if ($p['checks'] <= self::MAX_WAIT_CHECKS) {
                return response()->view('mycoolpay.waiting', ['retryUrl' => route('mycoolpay.return', 'success')]);
            }
        }
        $message = $state === 'SUCCESS' ? trans('lang.mycoolpay_mismatch')
            : (in_array($state, ['CREATED', 'PENDING', ''], true) ? trans('lang.mycoolpay_still_pending') : trans('lang.mycoolpay_not_paid'));
        Session::forget(MyCoolPay::SESSION);
        Session::save();
        return redirect($p['failUrl'] ?: '/')->with('mycoolpay_error', $message);
    }
}
