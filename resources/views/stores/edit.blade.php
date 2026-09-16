{{-- Edit one store. Identical form to create; the storeId from the route
     tells the shared behaviour which store to load and update. --}}
@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.mystore_plural') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('stores') !!}">{{ trans('lang.stores_table') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.store_edit') }}</li>
            </ol>
        </div>

        <div>
            <div class="card-body">
                <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">
                    {{ trans('lang.processing') }}
                </div>
                <div class="error_top" style="display:none"></div>
                <div id="noPermissionMsg" class="text-center text-danger font-weight-bold mb-3" style="display:none;">
                    <p>{{ trans("lang.no_permission") }}</p>
                </div>

                <div class="row vendor_payout_create">
                    <div class="vendor_payout_create-inner">
                        <div class="vendor_fieldset">
                            @include('stores.partials.form')
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group col-12 text-center btm-btn page-btn">
                <button type="button" class="btn btn-primary save_vendor_btn">
                    <i class="fa fa-save"></i> {{ trans('lang.save') }}
                </button>
                <a href="{!! route('stores') !!}" class="btn btn-default">
                    <i class="fa fa-undo"></i>{{ trans('lang.cancel') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@include('stores.partials.form_scripts')
@endsection
