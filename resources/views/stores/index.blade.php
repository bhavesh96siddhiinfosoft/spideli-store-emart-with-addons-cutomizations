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
                    <li class="breadcrumb-item active">{{ trans('lang.stores_table') }}</li>
                </ol>
            </div>
            <div>
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
                                <span class="icon mr-3"><img src="{{ asset('images/store_list.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.mystore_plural') }}</h3>
                                <span class="counter ml-3 total_count"></span>
                            </div>
                            <div class="d-flex top-title-right align-self-center">
                                <div class="select-box pl-3">
                                    <select class="form-control cuisine_selector filteredRecords">
                                        <option value="" disabled selected>{{ trans('lang.select_categoty') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card border">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="card card-box-with-icon bg--1">
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                <div class="card-box-with-content">
                                                    <h4 class="text-dark-2 mb-1 h4 rest_count">00</h4>
                                                    <p class="mb-0 small text-dark-2">{{ trans('lang.store_total') }}</p>
                                                </div>
                                                <span class="box-icon ab"><img src="{{ asset('images/restaurant_icon.png') }}"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card card-box-with-icon bg--5">
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                <div class="card-box-with-content">
                                                    <h4 class="text-dark-2 mb-1 h4 rest_active_count">00</h4>
                                                    <p class="mb-0 small text-dark-2">{{ trans('lang.store_active') }}</p>
                                                </div>
                                                <span class="box-icon ab"><img src="{{ asset('images/active_restaurant.png') }}"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card card-box-with-icon bg--8">
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                <div class="card-box-with-content">
                                                    <h4 class="text-dark-2 mb-1 h4 rest_inactive_count">00</h4>
                                                    <p class="mb-0 small text-dark-2">{{ trans('lang.store_inactive') }}</p>
                                                </div>
                                                <span class="box-icon ab"><img src="{{ asset('images/inactive_restaurant.png') }}"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card card-box-with-icon bg--6">
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                <div class="card-box-with-content">
                                                    <h4 class="text-dark-2 mb-1 h4 new_joined_rest">00</h4>
                                                    <p class="mb-0 small text-dark-2">{{ trans('lang.store_new_joined') }}</p>
                                                </div>
                                                <span class="box-icon ab"><img src="{{ asset('images/new_restaurant.png') }}"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-list">
                <div class="row">
                    <div class="col-12">
                        <div class="card border">
                            <div class="card-header d-flex justify-content-between align-items-center border-0">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.stores_table') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.stores_table_text') }}</p>
                                </div>
                                <div class="card-header-right d-flex align-items-center">
                                    <div class="card-header-btn mr-3">
                                        <a class="btn-primary btn rounded-full" href="{!! route('stores.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.store_create') }}</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive m-t-10">
                                    <table id="storeTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{ trans('lang.all') }}</a></label></th>
                                                <th>{{ trans('lang.actions') }}</th>
                                                <th>{{ trans('lang.store_info') }}</th>
                                                <th>{{ trans('lang.vendor_phone') }}</th>
                                                <th>{{ trans('lang.date') }}</th>
                                                <th>{{ trans('lang.store_items') }}</th>
                                                <th>{{ trans('lang.store_orders') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="append_list1">
                                        </tbody>
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
<script type="text/javascript">
    var database = firebase.firestore();
    var vendorUserId = "{{ $id }}";
    var authRole = "{{ $authRole }}";
    var empVendorId = "{{ $empVendorId }}";

    var placeholderImage = '';
    var categoriesById = {};
    var selectedCategory = '';

    /* Only this account's stores. A vendor's stores are `vendors` documents
     * sharing an `author`, so the list is derived rather than stored - see
     * docs/app-spec-multiple-stores.md. */
    var ref = database.collection('vendors').where('author', '==', vendorUserId);

    /* Columns the export writes, in table order. */
    var fieldConfig = [
        { title: "{{ trans('lang.store_info') }}", key: 'title' },
        { title: "{{ trans('lang.vendor_phone') }}", key: 'exportPhone' },
        { title: "{{ trans('lang.date') }}", key: 'exportDate' },
        { title: "{{ trans('lang.store_items') }}", key: 'items' },
        { title: "{{ trans('lang.store_orders') }}", key: 'orders' }
    ];

    $(document).ready(function () {
        jQuery("#data-table_processing").show();

        loadPlaceholder();
        loadCategories();

        $('.cuisine_selector').select2({
            placeholder: "{{ trans('lang.select_categoty') }}",
            allowClear: true,
            width: '100%'
        });

        buildTable();
    });

    async function loadPlaceholder() {
        var snapshot = await database.collection('settings').doc('placeHolderImage').get();
        if (snapshot.exists && snapshot.data().image) {
            placeholderImage = snapshot.data().image;
        }
    }

    /* The filter offers only the categories this account's stores actually use,
     * rather than every category on the platform - with a handful of stores a
     * full list would be mostly dead options. */
    async function loadCategories() {
        var snapshots = await database.collection('vendor_categories').where('publish', '==', true).get();

        snapshots.docs.forEach(function (doc) {
            var category = doc.data();
            categoriesById[category.id] = category.title;
        });

        var stores = await ref.get();
        var used = [];

        stores.docs.forEach(function (doc) {
            var ids = doc.data().categoryID || [];
            ids.forEach(function (id) {
                if (used.indexOf(id) === -1 && categoriesById[id]) {
                    used.push(id);
                }
            });
        });

        used.sort(function (a, b) {
            return (categoriesById[a] || '').localeCompare(categoriesById[b] || '');
        });

        used.forEach(function (id) {
            $('.cuisine_selector').append($("<option></option>").attr("value", id).text(categoriesById[id]));
        });
    }

    $(document).on('change', '.cuisine_selector', function () {
        selectedCategory = $(this).val() || '';
        $('#storeTable').DataTable().ajax.reload();
    });

    function buildTable() {
        const table = $('#storeTable').DataTable({
            pageLength: 10,
            processing: false,
            serverSide: true,
            responsive: true,
            ajax: async function (data, callback, settings) {
                const start = data.start;
                const length = data.length;
                const searchValue = data.search.value.toLowerCase();
                const orderColumnIndex = data.order.length ? data.order[0].column : 4;
                const orderDirection = data.order.length ? data.order[0].dir : 'desc';
                const orderableColumns = ['', '', 'title', 'phonenumber', 'createdAt', 'items', 'orders'];
                const orderByField = orderableColumns[orderColumnIndex];

                if (searchValue.length >= 3 || searchValue.length === 0) {
                    $('#data-table_processing').show();
                }

                try {
                    const querySnapshot = await ref.get();

                    if (querySnapshot.empty) {
                        $('.total_count').text(0);
                        $('.rest_count, .rest_active_count, .rest_inactive_count, .new_joined_rest').text(0);
                        $('#data-table_processing').hide();
                        callback({ draw: data.draw, recordsTotal: 0, recordsFiltered: 0, data: [] });
                        return;
                    }

                    /* The owner's own active flag decides whether their stores are
                     * live - a store has no separate switch of its own. */
                    const ownerActive = await isOwnerActive();

                    let filteredRecords = [];

                    await Promise.all(querySnapshot.docs.map(async function (doc) {
                        let childData = doc.data();
                        childData.id = childData.id ? childData.id : doc.id;

                        const phone = childData.phonenumber || '';
                        childData.exportPhone = phone;

                        childData.items = await countIn('vendor_products', childData.id);
                        childData.orders = await countIn('vendor_orders', childData.id);
                        childData.exportDate = formatDate(childData.createdAt);

                        if (selectedCategory !== '') {
                            const ids = childData.categoryID || [];
                            if (ids.indexOf(selectedCategory) === -1) {
                                return;
                            }
                        }

                        if (searchValue) {
                            const haystack = [
                                childData.title || '',
                                phone,
                                childData.exportDate
                            ].join(' ').toLowerCase();

                            if (haystack.indexOf(searchValue) === -1) {
                                return;
                            }
                        }

                        filteredRecords.push(childData);
                    }));

                    filteredRecords.sort((a, b) => {
                        let aValue = a[orderByField];
                        let bValue = b[orderByField];

                        if (orderByField === 'createdAt') {
                            aValue = toTime(a.createdAt);
                            bValue = toTime(b.createdAt);
                        } else if (orderByField === 'items' || orderByField === 'orders') {
                            aValue = parseInt(aValue) || 0;
                            bValue = parseInt(bValue) || 0;
                        } else {
                            aValue = aValue ? aValue.toString().toLowerCase() : '';
                            bValue = bValue ? bValue.toString().toLowerCase() : '';
                        }

                        if (orderDirection === 'asc') {
                            return (aValue > bValue) ? 1 : -1;
                        }
                        return (aValue < bValue) ? 1 : -1;
                    });

                    const totalRecords = filteredRecords.length;
                    const today = new Date().setHours(0, 0, 0, 0);
                    let newJoined = 0;

                    filteredRecords.forEach(function (childData) {
                        const created = toDate(childData.createdAt);
                        if (created != null && created.setHours(0, 0, 0, 0) === today) {
                            newJoined += 1;
                        }
                    });

                    $('.total_count').text(totalRecords);
                    $('.rest_count').text(totalRecords);
                    $('.rest_active_count').text(ownerActive ? totalRecords : 0);
                    $('.rest_inactive_count').text(ownerActive ? 0 : totalRecords);
                    $('.new_joined_rest').text(newJoined);

                    const paginatedRecords = filteredRecords.slice(start, start + length);
                    let records = paginatedRecords.map(buildHTML);

                    $('#data-table_processing').hide();

                    callback({
                        draw: data.draw,
                        recordsTotal: totalRecords,
                        recordsFiltered: totalRecords,
                        filteredData: filteredRecords,
                        data: records
                    });

                } catch (error) {
                    console.error("Error fetching stores:", error);
                    $('#data-table_processing').hide();
                    callback({ draw: data.draw, recordsTotal: 0, recordsFiltered: 0, data: [] });
                }
            },
            order: [[4, 'desc']],
            columnDefs: [
                { orderable: false, targets: [0, 1] }
            ],
            "language": datatableLang,
            dom: 'lfrtipB',
            buttons: [
                {
                    extend: 'collection',
                    text: '<i class="mdi mdi-cloud-download"></i> {{ trans('lang.export_as') }}',
                    className: 'btn btn-info',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '{{ trans('lang.export_excel') }}',
                            action: function (e, dt, button, config) {
                                exportData(dt, 'excel', fieldConfig);
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '{{ trans('lang.export_pdf') }}',
                            action: function (e, dt, button, config) {
                                exportData(dt, 'pdf', fieldConfig);
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            text: '{{ trans('lang.export_csv') }}',
                            action: function (e, dt, button, config) {
                                exportData(dt, 'csv', fieldConfig);
                            }
                        }
                    ]
                }
            ],
            initComplete: function () {
                $(".dataTables_filter").append($(".dt-buttons").detach());
                $('.dataTables_filter input').attr('placeholder', '{{ trans("lang.search_here") }}').attr('autocomplete', 'new-password').val('');
                $('.dataTables_filter label').contents().filter(function () {
                    return this.nodeType === 3;
                }).remove();
            }
        });
    }

    function buildHTML(val) {
        var html = [];
        var id = val.id;

        var routeEdit = '{{ route('stores.edit', ':id') }}'.replace(':id', id);
        var routeView = '{{ route('stores.view', ':id') }}'.replace(':id', id);

        html.push('<span class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '">' +
            '<label class="col-3 control-label" for="is_open_' + id + '"></label></span>');

        var actionHtml = '<span class="action-btn">';
        actionHtml += '<a href="' + routeView + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.view') }}"><i class="mdi mdi-eye"></i></a>';
        actionHtml += '<a href="' + routeEdit + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';
        actionHtml += '<a id="' + id + '" name="delete-btn" class="do_not_delete" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>';
        actionHtml += '</span>';
        html.push(actionHtml);

        var photo = val.photo ? val.photo : placeholderImage;
        html.push('<img alt="" width="100%" style="width:70px;height:70px;" src="' + photo +
            '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="image">' +
            '<a href="' + routeView + '" class="redirecttopage left_space">' + (val.title || '') + '</a>');

        html.push(val.phonenumber || '');

        var date = '';
        var time = '';
        var created = toDate(val.createdAt);
        if (created != null) {
            date = created.toDateString();
            time = created.toLocaleTimeString('en-US');
        }
        html.push(created != null ? '<span class="dt-time">' + date + '<br> ' + time + '</span>' : '');

        html.push(val.items);
        html.push(val.orders);

        return html;
    }

    /* Counted rather than read from a field: nothing maintains a running total on
     * the store record, so a stored one would go stale. */
    async function countIn(collection, storeId) {
        var snapshots = await database.collection(collection).where('vendorID', '==', storeId).get();

        return snapshots.size;
    }

    async function isOwnerActive() {
        var snapshot = await database.collection('users').doc(vendorUserId).get();

        return snapshot.exists ? snapshot.data().active !== false : true;
    }

    /* Deleting a store leaves its products and orders in place - they are history,
     * and an order that lost its store would be unreadable in the admin panel. */
    $(document).on('click', '[name="delete-btn"]', async function () {
        if (!confirm("{{ trans('lang.delete_store_confirm') }}")) {
            return;
        }

        jQuery("#data-table_processing").show();
        await database.collection('vendors').doc($(this).attr('id')).delete();
        $('#storeTable').DataTable().ajax.reload();
        jQuery("#data-table_processing").hide();
    });

    $(document).on('click', '#deleteAll', async function () {
        var ids = [];
        $('.is_open:checked').each(function () {
            ids.push($(this).attr('dataId'));
        });

        if (ids.length === 0) {
            return;
        }
        if (!confirm("{{ trans('lang.delete_store_confirm') }}")) {
            return;
        }

        jQuery("#data-table_processing").show();
        await Promise.all(ids.map(function (id) {
            return database.collection('vendors').doc(id).delete();
        }));
        $('#is_active').prop('checked', false);
        $('#storeTable').DataTable().ajax.reload();
        jQuery("#data-table_processing").hide();
    });

    $(document).on('click', '#is_active', function () {
        $('.is_open').prop('checked', $(this).is(':checked'));
    });

    $(document.body).on('click', '.redirecttopage', function () {
        var url = $(this).attr('data-url');
        if (url) {
            window.location.href = url;
        }
    });

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

    function toTime(value) {
        var date = toDate(value);
        return date == null ? 0 : date.getTime();
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
</script>
@endsection
