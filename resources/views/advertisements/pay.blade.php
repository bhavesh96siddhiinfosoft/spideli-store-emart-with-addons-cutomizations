@extends('layouts.app')
{{-- #113: the store pays its approved advert: days x the price per day of its zone, by wallet or a gateway. --}}
@php
    $cur = $q['currencyData'];
    $dec = \App\Services\AdvertPayment::decimals($q['currency']);
    $money = function ($v) use ($cur, $dec) {
        $t = number_format((float)$v, $dec, '.', ',');
        return !empty($cur['symbolAtRight']) ? $t . ' ' . ($cur['symbol'] ?? '') : ($cur['symbol'] ?? '') . $t;
    };
    $ad = $q['ad'];
@endphp
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.adv_pay_title') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('advertisements') !!}">{{ trans('lang.advertisement_plural') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.adv_pay_title') }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        @if($error)
            <div class="error_top"><p>{{ $error }}</p></div>
        @endif
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card border">
                    <div class="card-header bg-white"><h3 class="mb-0">{{ $ad['title'] ?? '' }}</h3></div>
                    <div class="card-body">
                        <p class="mb-1"><strong>{{ trans('lang.duration') }}:</strong>
                            {{ ($ad['startDate'] ?? null) instanceof \DateTimeInterface ? $ad['startDate']->format('d M Y') : '' }} -
                            {{ ($ad['endDate'] ?? null) instanceof \DateTimeInterface ? $ad['endDate']->format('d M Y') : '' }}</p>
                        <p class="mb-1"><strong>{{ trans('lang.adv_pay_days') }}:</strong> {{ $q['days'] }} × {{ $money($q['pricePerDay']) }} {{ trans('lang.adv_pay_per_day') }}</p>
                        <p class="h4 font-weight-bold my-3">{{ trans('lang.adv_pay_total') }}: <span class="adv_total">{{ $money($q['total']) }}</span></p>
                        @if(!$methods)
                            <div class="error_top"><p>{{ trans('lang.adv_pay_no_method') }}</p></div>
                        @else
                            <form method="POST" action="{{ route('advertisement-pay.pay', $q['adId']) }}">
                                @csrf
                                <h5 class="mt-3">{{ trans('lang.adv_pay_with') }}</h5>
                                @foreach($methods as $key => $label)
                                    <div class="form-check border rounded p-2 pl-5 mb-2">
                                        <input class="form-check-input" type="radio" name="method" id="m_{{ $key }}" value="{{ $key }}" {{ $loop->first ? 'checked' : '' }}>
                                        <label class="form-check-label" for="m_{{ $key }}">{{ $label }}
                                            @if($key === 'wallet')
                                                <small class="text-muted ml-2">{{ trans('lang.adv_pay_balance', ['amount' => $money($q['balance'])]) }}</small>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                                <button type="submit" class="btn btn-primary btn-block mt-3">{{ trans('lang.adv_pay_button', ['amount' => $money($q['total'])]) }}</button>
                            </form>
                        @endif
                        <a class="d-block text-center mt-3" href="{{ route('advertisements.view', $q['adId']) }}">{{ trans('lang.adv_pay_back') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
