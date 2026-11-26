@extends('layouts.app')
{{-- #113: the end of an advert payment. --}}
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.adv_pay_title') }}</h3>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border text-center p-4">
                    <h4 class="{{ $ok ? 'text-success' : 'text-danger' }}">{{ $ok ? trans('lang.adv_pay_success_title') : trans('lang.adv_pay_failed_title') }}</h4>
                    <p class="mb-3">{{ $message }}</p>
                    <div>
                        @if($adId)
                            <a class="btn btn-primary" href="{{ route('advertisements.view', $adId) }}">{{ trans('lang.adv_pay_back') }}</a>
                        @endif
                        <a class="btn btn-default" href="{{ route('advertisements') }}">{{ trans('lang.advertisement_plural') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
