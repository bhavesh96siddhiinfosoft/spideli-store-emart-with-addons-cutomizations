{{-- A store at a glance, read only. Edit is one click away; this screen exists
     so a vendor with several stores can check one without risking a change. --}}
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
                <li class="breadcrumb-item active">{{ trans('lang.store_info') }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">
            {{ trans('lang.processing') }}
        </div>
        <div class="error_top" style="display:none"></div>

        <div class="row">
            <div class="col-12">
                <div class="card border">
                    <div class="card-header d-flex justify-content-between align-items-center border-0">
                        <div class="card-header-title d-flex align-items-center">
                            <img id="store_photo" class="img-circle mr-3"
                                style="width:70px;height:70px;object-fit:cover;" src="">
                            <div>
                                <h3 class="text-dark-2 mb-1 h4 store_title"></h3>
                                <p class="mb-0 text-dark-2 store_section"></p>
                            </div>
                        </div>
                        <div class="card-header-right d-flex align-items-center">
                            <a class="btn-primary btn rounded-full" id="edit_link" href="#">
                                <i class="mdi mdi-lead-pencil mr-2"></i>{{ trans('lang.store_edit') }}
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">{{ trans('lang.vendor_phone') }}</th>
                                        <td class="store_phone"></td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('lang.region') }}</th>
                                        <td class="store_region"></td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('lang.zone') }}</th>
                                        <td class="store_zone"></td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('lang.vendor_address') }}</th>
                                        <td class="store_address"></td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('lang.cordinates') }}</th>
                                        <td class="store_coordinates"></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">{{ trans('lang.category_plural') }}</th>
                                        <td class="store_categories"></td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('lang.store_items') }}</th>
                                        <td class="store_items"></td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('lang.store_orders') }}</th>
                                        <td class="store_orders"></td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('lang.date') }}</th>
                                        <td class="store_created"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <h5 class="mt-3">{{ trans('lang.vendor_description') }}</h5>
                                <p class="store_description text-dark-2"></p>
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
    var storeId = "{{ $storeId }}";
    var authRole = "{{ $authRole }}";

    document.addEventListener("DOMContentLoaded", async function () {
        jQuery("#data-table_processing").show();

        $('#edit_link').attr('href', "{{ route('stores.edit', ':id') }}".replace(':id', storeId));

        var snapshot = await database.collection('vendors').doc(storeId).get();

        if (!snapshot.exists) {
            jQuery("#data-table_processing").hide();
            $(".error_top").show().html("<p>{{ trans('lang.vendor_record_not_found') }}</p>");
            return;
        }

        var store = snapshot.data();

        /* A vendor may only view their own stores. The id is in the URL, so the
         * check belongs here and not only on the list that linked here. */
        if (authRole === 'vendor' && store.author !== vendorUserId) {
            jQuery("#data-table_processing").hide();
            $(".error_top").show().html("<p>{{ trans('lang.no_permission') }}</p>");
            $('.card').hide();
            return;
        }

        await render(store);

        jQuery("#data-table_processing").hide();
    });

    async function render(store) {
        var placeholder = await database.collection('settings').doc('placeHolderImage').get();
        var placeholderImage = (placeholder.exists && placeholder.data().image) ? placeholder.data().image : '';

        $('#store_photo').attr('src', store.photo ? store.photo : placeholderImage);
        $('.store_title').text(store.title || '');
        $('.store_phone').text(store.phonenumber || '');
        $('.store_address').text(store.location || '');
        $('.store_description').text(store.description || '');
        $('.store_created').text(formatDate(store.createdAt));

        var lat = store.latitude !== undefined ? store.latitude : '';
        var lng = store.longitude !== undefined ? store.longitude : '';
        $('.store_coordinates').text(lat !== '' ? lat + ', ' + lng : '');

        $('.store_items').text(await countIn('vendor_products', store.id));
        $('.store_orders').text(await countIn('vendor_orders', store.id));

        await Promise.all([
            nameFrom('sections', store.section_id, '.store_section'),
            nameFrom('regions', store.regionId, '.store_region'),
            nameFrom('zone', store.zoneId, '.store_zone'),
            categoryNames(store.categoryID)
        ]);
    }

    async function nameFrom(collection, id, selector) {
        if (!id) {
            $(selector).text('');
            return;
        }

        var snapshot = await database.collection(collection).doc(id).get();

        if (!snapshot.exists) {
            $(selector).text('');
            return;
        }

        var data = snapshot.data();

        $(selector).text(data.name || data.title || '');
    }

    async function categoryNames(ids) {
        if (!ids || ids.length === 0) {
            $('.store_categories').text('');
            return;
        }

        var names = [];

        await Promise.all(ids.map(async function (id) {
            var snapshot = await database.collection('vendor_categories').doc(id).get();
            if (snapshot.exists) {
                names.push(snapshot.data().title || '');
            }
        }));

        $('.store_categories').text(names.join(', '));
    }

    async function countIn(collection, id) {
        var snapshots = await database.collection(collection).where('vendorID', '==', id).get();

        return snapshots.size;
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
</script>
@endsection
