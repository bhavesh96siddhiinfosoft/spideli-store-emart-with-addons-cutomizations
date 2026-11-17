<?php

namespace App\Http\Controllers;

use App\Services\MyPVit;
use Illuminate\Support\Facades\Session;

/**
 * #92: PVit brings the customer back to the website, which forwards them here
 * (its /mypvit/handoff remembered this page). The payment is accepted only when
 * the website reports it SUCCESS and verified with PVit's status API, for the
 * same reference and amount.
 */
class MyPVitController extends Controller
{
    const MAX_WAIT_CHECKS = 24;   // about 2 minutes at one check every 5 seconds

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function back($result)
    {
        $p = Session::get(MyPVit::SESSION);
        if (!$p) {
            return redirect('/')->with('mycoolpay_error', trans('lang.mypvit_no_pending'));
        }
        $status = MyPVit::remoteStatus($p);
        if (MyPVit::proves($status, $p)) {
            $p['confirmed'] = true;
            Session::put(MyPVit::SESSION, $p);
            Session::save();
            $url = $p['successUrl'];
            return redirect($url . (strpos($url, '?') === false ? '?' : '&') . 'mypvit_token=' . $p['confirmToken']);
        }
        $state = strtoupper((string)($status['status'] ?? ''));
        if (empty($status['verified'])) {
            $state = 'PENDING';   // what PVit's status API has not confirmed yet is still pending
        }
        if (in_array($state, ['', 'CREATED', 'PENDING', 'AMBIGUOUS'], true) && $result === 'success') {
            $p['checks'] = (int)($p['checks'] ?? 0) + 1;
            Session::put(MyPVit::SESSION, $p);
            Session::save();
            if ($p['checks'] <= self::MAX_WAIT_CHECKS) {
                return response()->view('payment.waiting', ['retryUrl' => route('mypvit.return', 'success'), 'text' => trans('lang.mypvit_waiting_text')]);
            }
        }
        $message = $state === 'SUCCESS' ? trans('lang.mycoolpay_mismatch')
            : (in_array($state, ['', 'CREATED', 'PENDING', 'AMBIGUOUS'], true) && $result === 'success' ? trans('lang.mypvit_still_pending') : trans('lang.mycoolpay_not_paid'));
        Session::forget(MyPVit::SESSION);
        Session::save();
        return redirect($p['failUrl'] ?: '/')->with('mycoolpay_error', $message);
    }
}
