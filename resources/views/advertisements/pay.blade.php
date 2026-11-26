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
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <h3 class="mb-0">{{ $ad['title'] ?? '' }}</h3>
                        <span class="badge badge-success py-2 px-3">{{ trans('lang.approved') }}</span>
                    </div>
                    <div class="card-body">
                        {{-- #113: the advert in full before paying --}}
                        @php
                            $img = ($ad['type'] ?? '') === 'restaurant_promotion' ? (string)($ad['profileImage'] ?? '') : '';
                            $storeName = (string)($q['store']['title'] ?? '');
                            $fmt = function ($d) { return $d instanceof \DateTimeInterface ? $d->format('d M Y') : ''; };
                        @endphp
                        <div class="d-flex align-items-start mb-3 adv-pay-details">
                            @if($img !== '' && stripos($img, 'https://') === 0)
                                <img src="{{ $img }}" alt="" width="80" height="80" class="rounded mr-3" style="object-fit:cover;">
                            @endif
                            <div class="flex-grow-1">
                                @if(!empty($ad['description']))
                                    <p class="text-muted mb-2">{{ $ad['description'] }}</p>
                                @endif
                                <table class="table table-sm table-borderless mb-0">
                                    <tr><th class="pl-0" style="width:40%">{{ trans('lang.adv_store') }}</th>
                                        <td>{{ $storeName }}{{ $q['regionName'] !== '' ? ' · ' . $q['regionName'] : '' }}</td></tr>
                                    @if($q['zoneName'] !== '')
                                        <tr><th class="pl-0">{{ trans('lang.adv_zone') }}</th><td>{{ $q['zoneName'] }}</td></tr>
                                    @endif
                                    <tr><th class="pl-0">{{ trans('lang.advertisement_type') }}</th>
                                        <td>{{ ($ad['type'] ?? '') === 'video_promotion' ? trans('lang.video_promotion') : trans('lang.restaurant_promotion') }}</td></tr>
                                    <tr><th class="pl-0">{{ trans('lang.duration') }}</th>
                                        <td>{{ $fmt($ad['startDate'] ?? null) }} - {{ $fmt($ad['endDate'] ?? null) }}</td></tr>
                                    <tr><th class="pl-0">{{ trans('lang.adv_pay_days') }}</th>
                                        <td>{{ $q['days'] }} × {{ $money($q['pricePerDay']) }} {{ trans('lang.adv_pay_per_day') }}</td></tr>
                                </table>
                            </div>
                        </div>
                        <p class="h4 font-weight-bold my-3">{{ trans('lang.adv_pay_total') }}: <span class="adv_total">{{ $money($q['total']) }}</span></p>
                        {{-- #113: every payment method switched on and "Available in Regions" for the store's region
                             (Settings > Payment Methods); one that cannot take the payment yet is shown greyed with why --}}
                        @php $firstUsable = array_key_first($methods); @endphp
                        <form method="POST" action="{{ route('advertisement-pay.pay', $q['adId']) }}">
                            @csrf
                            <h5 class="mt-3">{{ trans('lang.adv_pay_with') }}</h5>
                            @foreach($options as $key => $o)
                                <div class="form-check border rounded p-2 pl-5 mb-2 {{ $o['usable'] ? '' : 'bg-light text-muted' }}">
                                    <input class="form-check-input" type="radio" name="method" id="m_{{ $key }}" value="{{ $key }}"
                                        {{ $key === $firstUsable ? 'checked' : '' }} {{ $o['usable'] ? '' : 'disabled' }}>
                                    <label class="form-check-label" for="m_{{ $key }}">{{ $o['label'] }}
                                        @if($key === 'wallet')
                                            <small class="text-muted ml-2">{{ trans('lang.adv_pay_balance', ['amount' => $money($q['balance'])]) }}</small>
                                        @endif
                                        @if(!$o['usable'])
                                            <small class="ml-2 adv-pay-note">{{ $o['note'] }}</small>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                            @if(!$methods)
                                <div class="error_top"><p>{{ trans('lang.adv_pay_no_method') }}</p></div>
                            @else
                                <button type="submit" class="btn btn-primary btn-block mt-3">{{ trans('lang.adv_pay_button', ['amount' => $money($q['total'])]) }}</button>
                            @endif
                        </form>
                        <a class="d-block text-center mt-3" href="{{ route('advertisements.view', $q['adId']) }}">{{ trans('lang.adv_pay_back') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
