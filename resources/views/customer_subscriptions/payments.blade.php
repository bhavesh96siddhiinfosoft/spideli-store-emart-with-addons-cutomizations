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
                <li class="breadcrumb-item active">{{ trans('lang.customer_subscription_payments') }}</li>
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
                            <span class="icon mr-3"><i class="mdi mdi-cash-multiple mdi-24px"></i></span>
                            <h3 class="mb-0">{{ trans('lang.customer_subscription_payments') }}</h3>
                            <span class="counter ml-3 total_count"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('customer_subscriptions.partials.tabs', ['active' => 'payments'])

        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.customer_subscription_payments') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.customer_subscription_payments_text') }}</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="payments_table"
                                    class="display nowrap table table-hover table-striped table-bordered"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('lang.subscriber_name') }}</th>
                                            <th>{{ trans('lang.plan_title') }}</th>
                                            <th>{{ trans('lang.payment_amount') }}</th>
                                            <th>{{ trans('lang.payment_commission') }}</th>
                                            <th>{{ trans('lang.payment_vendor_earning') }}</th>
                                            <th>{{ trans('lang.payment_method_label') }}</th>
                                            <th>{{ trans('lang.date') }}</th>
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

    var currentCurrency = '';
    var currencyAtRight = false;
    var decimal_degits = 0;

    document.addEventListener("DOMContentLoaded", async function () {
        jQuery("#data-table_processing").show();

        if (!await employeeMayView()) {
            return;
        }

        var currencies = await database.collection('currencies').where('isActive', '==', true).get();
        if (!currencies.empty) {
            var currencyData = currencies.docs[0].data();
            currentCurrency = currencyData.symbol;
            currencyAtRight = currencyData.symbolAtRight;
            if (currencyData.decimal_degits) {
                decimal_degits = currencyData.decimal_degits;
            }
        }

        var vendor = await loadVendorRecord();
        if (vendor == null) {
            jQuery("#data-table_processing").hide();
            return;
        }

        await loadPayments(vendor.id);

        jQuery("#data-table_processing").hide();
    });

    async function loadPayments(vendorID) {
        var snapshots = await database.collection('vendor_subscription_payments')
            .where('vendorID', '==', vendorID).get();

        var rows = [];

        await Promise.all(snapshots.docs.map(async function (doc) {
            var payment = doc.data();

            var customerName = '';
            if (payment.customerId) {
                var customer = await database.collection('users').doc(payment.customerId).get();
                if (customer.exists) {
                    var customerData = customer.data();
                    customerName = ((customerData.firstName || '') + ' ' + (customerData.lastName || '')).trim();
                }
            }

            var planTitle = '';
            if (payment.planId) {
                var plan = await database.collection('vendor_subscription_plans').doc(payment.planId).get();
                if (plan.exists) {
                    planTitle = plan.data().title || '';
                }
            }

            rows.push([
                customerName,
                planTitle,
                formatPrice(payment.amount),
                /* The commission shown is the one recorded on the payment, not
                 * the store's current rate - the platform changing its cut must
                 * not rewrite what an old payment earned. */
                formatPrice(payment.adminCommission),
                formatPrice(payment.vendorEarning),
                payment.payment_method || '',
                formatDate(payment.createdAt)
            ]);
        }));

        $('.total_count').text(rows.length);

        $('#payments_table').DataTable({
            data: rows,
            pageLength: 10,
            responsive: true,
            order: [[6, 'desc']],
            language: {
                emptyTable: "{{ trans('lang.no_subscription_payments_yet') }}"
            }
        });
    }

    function formatPrice(value) {
        var amount = parseFloat(value || 0).toFixed(decimal_degits);
        return currencyAtRight ? amount + '' + currentCurrency : currentCurrency + '' + amount;
    }

    function formatDate(value) {
        if (!value) {
            return '';
        }

        var date = (typeof value.toDate === 'function') ? value.toDate() : new Date(value);
        if (isNaN(date.getTime())) {
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

    /* An employee reaches these screens only if their role allows it. The menu
     * already hides the entry, but the routes are reachable by URL, so the check
     * belongs on the screen as well - the same pattern the other employee-gated
     * screens use. */
    async function employeeMayView() {
        if (authRole !== 'employee') {
            return true;
        }

        var perm = await getEmployeePermissionForTitle(vendorUserId, "Customer Subscriptions");

        if (perm && perm.isActive) {
            return true;
        }

        alert('{{ trans("lang.no_permission") }}');
        jQuery("#data-table_processing").hide();
        $('.page-menu, .vendor_payout_create, .btm-btn').html(
            '<p class="text-center text-danger font-weight-bold">{{ trans("lang.no_permission") }}</p>'
        );

        return false;
    }
</script>
@endsection
