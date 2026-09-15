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
                <li class="breadcrumb-item"><a href="{!! route('customer-subscriptions') !!}">{{ trans('lang.customer_subscription_plans') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.customer_subscription_edit') }}</li>
            </ol>
        </div>

        <div>
            <div class="card-body">
                <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">
                    {{ trans('lang.processing') }}
                </div>
                <div class="error_top" style="display:none"></div>

                <div class="row vendor_payout_create">
                    <div class="vendor_payout_create-inner">
                        @include('customer_subscriptions.partials.form')
                    </div>
                </div>
            </div>

            <div class="form-group col-12 text-center btm-btn">
                <button type="button" class="btn btn-primary save_plan_btn">
                    <i class="fa fa-save"></i> {{ trans('lang.save') }}
                </button>
                <a href="{!! route('customer-subscriptions') !!}" class="btn btn-default">
                    <i class="fa fa-undo"></i>{{ trans('lang.cancel') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var database = firebase.firestore();
    var planId = "{{ $id }}";
    var vendorUserId = "{{ $vendorUserId }}";
    var authRole = "{{ $authRole }}";
    var empVendorId = "{{ $empVendorId }}";

    var planPhoto = '';
    var planFileName = '';
    var existingPhoto = '';
    var storageRef = firebase.storage().ref('images');

    document.addEventListener("DOMContentLoaded", async function () {
        jQuery("#data-table_processing").show();

        if (!await employeeMayView()) {
            return;
        }

        var snapshot = await database.collection('vendor_subscription_plans').doc(planId).get();
        if (!snapshot.exists) {
            jQuery("#data-table_processing").hide();
            showError("{{ trans('lang.no_plans_yet') }}");
            return;
        }

        var plan = snapshot.data();

        $(".plan_title").val(plan.title);
        $(".plan_price").val(plan.price);
        $(".plan_period").val(String(plan.expiryDay) === '365' ? '365' : '30');
        $(".plan_description").val(plan.description);
        $(".plan_enabled").prop('checked', plan.isEnable === true);

        if (plan.photo) {
            existingPhoto = plan.photo;
            $("#plan_image").attr('src', plan.photo);
            $(".plan_image_preview").show();
        }

        jQuery("#data-table_processing").hide();
    });

    $(".save_plan_btn").click(async function () {
        $(".error_top").hide();

        var title = $(".plan_title").val();
        var price = $(".plan_price").val();
        var expiryDay = $(".plan_period").val();
        var description = $(".plan_description").val();
        var isEnable = $(".plan_enabled").is(":checked");

        if (title == '') {
            showError("{{ trans('lang.enter_plan_title_error') }}");
            return;
        }
        if (price == '' || parseFloat(price) <= 0) {
            showError("{{ trans('lang.enter_plan_price_error') }}");
            return;
        }

        jQuery("#data-table_processing").show();

        var image = await storePlanImage();

        /* Only the fields on this form are written. vendorID, regionId and
         * sectionId stay as they were set at creation - a plan does not change
         * hands. Existing subscribers are unaffected either way: they hold a
         * snapshot of the plan as it was when they paid. */
        await database.collection('vendor_subscription_plans').doc(planId).update({
            'title': title,
            'description': description,
            'photo': image,
            'price': price,
            'expiryDay': expiryDay,
            'isEnable': isEnable
        });

        window.location.href = '{{ route('customer-subscriptions') }}';
    });

    function showError(message) {
        jQuery("#data-table_processing").hide();
        $(".error_top").show().html("<p>" + message + "</p>");
        window.scrollTo(0, 0);
    }

    function handlePlanFileSelect(evt) {
        var f = evt.target.files[0];
        if (!f) {
            planPhoto = '';
            planFileName = '';
            return;
        }

        var reader = new FileReader();
        reader.onload = (function () {
            return function (e) {
                var ext = f.name.split('.').pop();
                var name = f.name.replace(/C:\\fakepath\\/i, '').split('.')[0];

                planPhoto = e.target.result;
                planFileName = name + "_" + Number(new Date()) + '.' + ext;

                $("#plan_image").attr('src', planPhoto);
                $(".plan_image_preview").show();
            };
        })(f);
        reader.readAsDataURL(f);
    }

    /* Keeps the image already on the plan when none was chosen, so saving the
     * form without touching the file field does not clear it. */
    async function storePlanImage() {
        if (planPhoto == '') {
            return existingPhoto;
        }

        try {
            var payload = planPhoto.replace(/^data:image\/[a-z]+;base64,/, "");
            var uploadTask = await storageRef.child(planFileName)
                .putString(payload, 'base64', { contentType: 'image/jpg' });

            return await uploadTask.ref.getDownloadURL();
        } catch (error) {
            console.error("Error uploading plan image:", error);
            return existingPhoto;
        }
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
