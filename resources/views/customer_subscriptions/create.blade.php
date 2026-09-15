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
                <li class="breadcrumb-item active">{{ trans('lang.customer_subscription_create') }}</li>
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
    var vendorUserId = "{{ $id }}";
    var authRole = "{{ $authRole }}";
    var empVendorId = "{{ $empVendorId }}";

    var planPhoto = '';
    var planFileName = '';
    var storageRef = firebase.storage().ref('images');

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

        var vendor = await loadVendorRecord();
        if (vendor == null) {
            jQuery("#data-table_processing").hide();
            showError("{{ trans('lang.vendor_record_not_found') }}");
            return;
        }

        var image = await storePlanImage();
        var id = database.collection('tmp').doc().id;

        /* regionId and sectionId are taken from the store, not asked for: a plan
         * belongs to one store, and a store belongs to one region and one
         * section. Without them the plan is invisible to a region-filtered
         * admin. */
        await database.collection('vendor_subscription_plans').doc(id).set({
            'id': id,
            'vendorID': vendor.id,
            'regionId': vendor.regionId ? vendor.regionId : '',
            'sectionId': vendor.section_id ? vendor.section_id : '',
            'title': title,
            'description': description,
            'photo': image,
            'price': price,
            'expiryDay': expiryDay,
            'isEnable': isEnable,
            'createdAt': firebase.firestore.FieldValue.serverTimestamp()
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
            $(".plan_image_preview").hide();
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

    async function storePlanImage() {
        if (planPhoto == '') {
            return '';
        }

        try {
            var payload = planPhoto.replace(/^data:image\/[a-z]+;base64,/, "");
            var uploadTask = await storageRef.child(planFileName)
                .putString(payload, 'base64', { contentType: 'image/jpg' });

            return await uploadTask.ref.getDownloadURL();
        } catch (error) {
            console.error("Error uploading plan image:", error);
            return '';
        }
    }

    /* Named apart from the per-page getVendorId() helpers elsewhere in the panel,
     * and returns the whole record - the plan needs the store's region and
     * section, not only its id. */
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
