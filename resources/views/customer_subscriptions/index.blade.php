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
                <li class="breadcrumb-item active">{{ trans('lang.customer_subscription_plans') }}</li>
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
                            <span class="icon mr-3"><i class="mdi mdi-wallet-membership mdi-24px"></i></span>
                            <h3 class="mb-0">{{ trans('lang.customer_subscription_plans') }}</h3>
                            <span class="counter ml-3 total_count"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('customer_subscriptions.partials.tabs', ['active' => 'plans'])

        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.customer_subscription_plans') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.customer_subscription_plans_text') }}</p>
                            </div>
                            <div class="card-header-right d-flex align-items-center">
                                <div class="card-header-btn mr-3">
                                    <a class="btn-primary btn rounded-full" href="{!! route('customer-subscriptions.create') !!}">
                                        <i class="mdi mdi-plus mr-2"></i>{{ trans('lang.customer_subscription_create') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="plans_table"
                                    class="display nowrap table table-hover table-striped table-bordered"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('lang.plan_title') }}</th>
                                            <th>{{ trans('lang.plan_price') }}</th>
                                            <th>{{ trans('lang.plan_period') }}</th>
                                            <th>{{ trans('lang.plan_subscriber_count') }}</th>
                                            <th>{{ trans('lang.plan_enabled') }}</th>
                                            <th>{{ trans('lang.actions') }}</th>
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
    var vendorID = '';

    var currentCurrency = '';
    var currencyAtRight = false;
    var decimal_degits = 0;

    storeCurrencyRef().get().then(function (snapshots) {
        var currencyData = snapshots.docs[0].data();
        currentCurrency = currencyData.symbol;
        currencyAtRight = currencyData.symbolAtRight;
        if (currencyData.decimal_degits) {
            decimal_degits = currencyData.decimal_degits;
        }
    });

    document.addEventListener("DOMContentLoaded", async function () {
        jQuery("#data-table_processing").show();

        if (!await employeeMayView()) {
            return;
        }

        var vendor = await loadVendorRecord();
        if (vendor == null) {
            jQuery("#data-table_processing").hide();
            return;
        }
        vendorID = vendor.id;

        await loadPlans();

        jQuery("#data-table_processing").hide();
    });

    /* A store has a handful of plans, so they are fetched once and paged in the
     * browser rather than through the server-side machinery the larger lists
     * use. */
    async function loadPlans() {
        var snapshots = await database.collection('vendor_subscription_plans')
            .where('vendorID', '==', vendorID).get();

        var editUrlTemplate = "{{ route('customer-subscriptions.edit', ':id') }}";
        var rows = [];

        await Promise.all(snapshots.docs.map(async function (doc) {
            var plan = doc.data();

            var subscribers = await database.collection('vendor_subscriptions')
                .where('planId', '==', plan.id)
                .where('status', '==', 'active')
                .get();

            var actions = '<a class="btn btn-sm btn-outline-primary mr-1" href="' +
                editUrlTemplate.replace(':id', plan.id) + '"><i class="mdi mdi-pencil"></i></a>' +
                '<button type="button" class="btn btn-sm btn-outline-danger delete_plan" data-id="' +
                plan.id + '"><i class="mdi mdi-delete"></i></button>';

            rows.push([
                plan.title || '',
                formatPrice(plan.price),
                periodLabel(plan.expiryDay),
                subscribers.size,
                plan.isEnable === true ?
                    '<span class="badge badge-success">{{ trans('lang.plan_enabled') }}</span>' :
                    '<span class="badge badge-danger">&mdash;</span>',
                actions
            ]);
        }));

        $('.total_count').text(rows.length);

        $('#plans_table').DataTable({
            data: rows,
            pageLength: 10,
            responsive: true,
            order: [[0, 'asc']],
            columnDefs: [{ orderable: false, targets: 5 }],
            language: {
                emptyTable: "{{ trans('lang.no_plans_yet') }}"
            }
        });
    }

    /* Deleting a plan leaves existing subscriptions alone - they hold their own
     * snapshot of the plan, so a subscriber keeps what they paid for until it
     * expires. */
    $(document).on('click', '.delete_plan', async function () {
        if (!confirm("{{ trans('lang.delete_plan_confirm') }}")) {
            return;
        }

        jQuery("#data-table_processing").show();
        await database.collection('vendor_subscription_plans').doc($(this).attr('data-id')).delete();
        window.location.reload();
    });

    function periodLabel(expiryDay) {
        if (String(expiryDay) === '365') {
            return "{{ trans('lang.plan_period_annual') }}";
        }
        return "{{ trans('lang.plan_period_monthly') }}";
    }

    function formatPrice(value) {
        var amount = parseFloat(value || 0).toFixed(decimal_degits);
        return currencyAtRight ? amount + '' + currentCurrency : currentCurrency + '' + amount;
    }

    /* Delegates to resolveCurrentStore() in layouts/app.blade.php - the one place
     * that knows which store the panel is working on. Returns the whole record,
     * since the plan screens need the store's region and section as well. */
    async function loadVendorRecord() {
        return await resolveCurrentStore(vendorUserId);
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
