@extends('layouts.app')
{{-- #113: Razorpay Checkout for the advert's checkout. The order was created on the server; this page gets the key id
     only. Razorpay's answer goes to the done page, where the server checks the signature and the payment itself. --}}
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.adv_pay_razorpay_title') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('advertisements') !!}">{{ trans('lang.advertisement_plural') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.adv_pay_razorpay_title') }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card border">
                    <div class="card-body text-center">
                        <p class="adv-rzp-note d-none text-danger">{{ trans('lang.adv_pay_razorpay_closed') }}</p>
                        <button type="button" class="btn btn-primary btn-block" id="adv_rzp_open">{{ trans('lang.adv_pay_razorpay_open') }}</button>
                        <a class="d-block mt-3" href="{{ route('advertisement-pay.failed', $checkoutId) }}">{{ trans('lang.adv_pay_cancel') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script type="text/javascript">
        (function () {
            var doneUrl = @json(route('advertisement-pay.done', $checkoutId));
            var options = {
                key: @json($key),
                amount: @json($amountMinor),
                currency: @json((string)$checkout['currency']),
                order_id: @json((string)$checkout['gatewayOrderId']),
                name: 'Spideli',
                description: @json(trans('lang.adv_pay_title')),
                prefill: {name: @json((string)$customer['name']), email: @json((string)$customer['email'])},
                handler: function (r) {
                    // the server checks all of this with Razorpay before marking the advert paid
                    var q = new URLSearchParams({
                        razorpay_payment_id: r.razorpay_payment_id || '',
                        razorpay_order_id: r.razorpay_order_id || '',
                        razorpay_signature: r.razorpay_signature || ''
                    });
                    window.location.href = doneUrl + '?' + q.toString();
                },
                modal: {ondismiss: function () { $('.adv-rzp-note').removeClass('d-none'); }}
            };
            function open() {
                if (typeof Razorpay !== 'function') {
                    $('.adv-rzp-note').removeClass('d-none');
                    return;
                }
                new Razorpay(options).open();
            }
            document.getElementById('adv_rzp_open').addEventListener('click', open);
            window.addEventListener('load', open);
        })();
    </script>
@endsection
