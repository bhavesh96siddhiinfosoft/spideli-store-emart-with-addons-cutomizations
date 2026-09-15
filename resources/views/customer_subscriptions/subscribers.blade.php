@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.customer_subscription_plural') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.customer_subscription_subscribers') }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid page-menu">
        <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">
            {{ trans('lang.processing') }}
        </div>

        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><i class="mdi mdi-account-multiple mdi-24px"></i></span>
                            <h3 class="mb-0">{{ trans('lang.customer_subscription_subscribers') }}</h3>
                            <span class="counter ml-3 total_count"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('customer_subscriptions.partials.tabs', ['active' => 'subscribers'])

        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.customer_subscription_subscribers') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.customer_subscription_subscribers_text') }}</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="subscribers_table"
                                    class="display nowrap table table-hover table-striped table-bordered"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('lang.subscriber_name') }}</th>
                                            <th>{{ trans('lang.plan_title') }}</th>
                                            <th>{{ trans('lang.subscription_start_date') }}</th>
                                            <th>{{ trans('lang.subscription_expiry_date') }}</th>
                                            <th>{{ trans('lang.subscription_status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var database = firebase.firestore();
    var vendorUserId = "{{ $id }}";
    var authRole = "{{ $authRole }}";
    var empVendorId = "{{ $empVendorId }}";

    document.addEventListener("DOMContentLoaded", async function () {
        jQuery("#data-table_processing").show();

        var vendor = await loadVendorRecord();
        if (vendor == null) {
            jQuery("#data-table_processing").hide();
            return;
        }

        await loadSubscribers(vendor.id);

        jQuery("#data-table_processing").hide();
    });

    async function loadSubscribers(vendorID) {
        var snapshots = await database.collection('vendor_subscriptions')
            .where('vendorID', '==', vendorID).get();

        var rows = [];

        await Promise.all(snapshots.docs.map(async function (doc) {
            var subscription = doc.data();

            var customerName = '';
            if (subscription.customerId) {
                var customer = await database.collection('users').doc(subscription.customerId).get();
                if (customer.exists) {
                    var customerData = customer.data();
                    customerName = ((customerData.firstName || '') + ' ' + (customerData.lastName || '')).trim();
                }
            }

            /* The plan name comes from the snapshot stored on the subscription,
             * not from the plan document: a store renaming or deleting a plan
             * must not change what an existing subscriber is shown. */
            var planTitle = (subscription.plan && subscription.plan.title) ? subscription.plan.title : '';

            rows.push([
                customerName,
                planTitle,
                formatDate(subscription.startDate),
                formatDate(subscription.expiryDate),
                statusBadge(subscription)
            ]);
        }));

        $('.total_count').text(rows.length);

        $('#subscribers_table').DataTable({
            data: rows,
            pageLength: 10,
            responsive: true,
            order: [[3, 'desc']],
            language: {
                emptyTable: "{{ trans('lang.no_subscribers_yet') }}"
            }
        });
    }

    /* A subscription whose expiry has passed reads as expired even if nothing
     * has run to change its stored status - there is no scheduled job, so the
     * date is the truth. */
    function statusBadge(subscription) {
        var status = subscription.status || 'active';

        if (status === 'cancelled') {
            return '<span class="badge badge-danger">{{ trans('lang.subscription_status_cancelled') }}</span>';
        }

        var expiry = toDate(subscription.expiryDate);
        if (status === 'expired' || (expiry != null && expiry.getTime() < Date.now())) {
            return '<span class="badge badge-warning">{{ trans('lang.subscription_status_expired') }}</span>';
        }

        return '<span class="badge badge-success">{{ trans('lang.subscription_status_active') }}</span>';
    }

    function toDate(value) {
        if (!value) {
            return null;
        }
        if (typeof value.toDate === 'function') {
            return value.toDate();
        }
        var parsed = new Date(value);
        return isNaN(parsed.getTime()) ? null : parsed;
    }

    function formatDate(value) {
        var date = toDate(value);
        if (date == null) {
            return '';
        }

        var day = ('0' + date.getDate()).slice(-2);
        var month = ('0' + (date.getMonth() + 1)).slice(-2);

        return day + '-' + month + '-' + date.getFullYear();
    }

    async function loadVendorRecord() {
        var snapshots = authRole === 'vendor' ?
            await database.collection('vendors').where('author', '==', vendorUserId).get() :
            await database.collection('vendors').where('id', '==', empVendorId).get();

        if (snapshots.empty) {
            return null;
        }

        return snapshots.docs[0].data();
    }
</script>
@endsection
