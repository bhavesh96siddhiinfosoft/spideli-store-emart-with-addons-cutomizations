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
                                <div class="select-box pl-3 store-filter" id="region_filter_box">
                                    <select class="form-control region_selector filteredRecords">
                                        <option value="" disabled selected>{{ trans('lang.select_region_filter') }}</option>
                                    </select>
                                </div>
                                <div class="select-box pl-3 store-filter" id="category_filter_box">
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
                                                <th>{{ trans('lang.region') }}</th>
                                                <th>{{ trans('lang.store_status') }}</th>
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
    var selectedStoreId = '';
    var selectedRegion = '';
    var regionNames = {};

    /* Only this account's stores. A vendor's stores are `vendors` documents
     * sharing an `author`, so the list is derived rather than stored - see
     * docs/app-spec-multiple-stores.md. */
    var ref = database.collection('vendors').where('author', '==', vendorUserId);

    /* Columns the export writes, in table order. */
    var fieldConfig = [
        { title: "{{ trans('lang.store_info') }}", key: 'title' },
        { title: "{{ trans('lang.region') }}", key: 'exportRegion' },
        { title: "{{ trans('lang.store_status') }}", key: 'exportStatus' },
        { title: "{{ trans('lang.vendor_phone') }}", key: 'exportPhone' },
        { title: "{{ trans('lang.date') }}", key: 'exportDate' },
        { title: "{{ trans('lang.store_items') }}", key: 'items' },
        { title: "{{ trans('lang.store_orders') }}", key: 'orders' }
    ];

    $(document).ready(function () {
        jQuery("#data-table_processing").show();

        loadSelectedStore();
        loadPlaceholder();
        loadCategories();
        loadRegions();

        /* A fixed width rather than 'resolve' or '100%'. Left to itself select2
         * measures the plain `.form-control`, which is 100% of a header that is
         * most of the page wide - the pill stayed at its 150px min-width while
         * the panel below it inherited the full measurement, so it hung off the
         * right edge and pushed the page scrollbar out. One width keeps the
         * pill, the panel and the options in line. */
        var filterWidth = '190px';

        /* `dropdownParent` anchors the panel to the pill's own wrapper. Left on
         * its default select2 hangs it off <body> and places it by measuring the
         * pill once, so it stayed where it was drawn while the page scrolled -
         * that is what put it over the header. Inside the wrapper it is laid out
         * with the pill and moves with it. */
        $('.region_selector').select2({
            placeholder: "{{ trans('lang.select_region_filter') }}",
            dropdownParent: $('#region_filter_box'),
            minimumResultsForSearch: Infinity,
            allowClear: true,
            width: filterWidth
        });

        $('.cuisine_selector').select2({
            placeholder: "{{ trans('lang.select_categoty') }}",
            dropdownParent: $('#category_filter_box'),
            minimumResultsForSearch: Infinity,
            allowClear: true,
            width: filterWidth
        });

        /* Clearing a filter otherwise reopens the dropdown it just cleared. */
        $('.region_selector, .cuisine_selector').on('select2:unselecting', function () {
            var self = $(this);
            setTimeout(function () {
                self.select2('close');
            }, 0);
        });

        buildTable();
    });

    /* Which store the panel is currently working on, so the list can mark it and
     * offer the others. */
    async function loadSelectedStore() {
        var snapshot = await database.collection('users').doc(vendorUserId).get();

        if (snapshot.exists) {
            selectedStoreId = snapshot.data().vendorID || '';
        }
    }

    async function loadPlaceholder() {
        var snapshot = await database.collection('settings').doc('placeHolderImage').get();
        if (snapshot.exists && snapshot.data().image) {
            placeholderImage = snapshot.data().image;
        }
    }

    /* Every published category, not only those already in use - a vendor
     * filtering their stores may well be looking for one that has none yet. */
    async function loadCategories() {
        var snapshots = await database.collection('vendor_categories').where('publish', '==', true).get();

        var categories = [];

        snapshots.docs.forEach(function (doc) {
            var category = doc.data();
            categoriesById[category.id] = category.title;
            categories.push(category);
        });

        categories.sort(function (a, b) {
            return (a.title || '').localeCompare(b.title || '');
        });

        categories.forEach(function (category) {
            $('.cuisine_selector').append($("<option></option>").attr("value", category.id).text(category.title));
        });
    }

    /* Every published region, for the same reason as the categories above. */
    async function loadRegions() {
        var snapshots = await database.collection('regions').get();

        var regions = [];

        snapshots.docs.forEach(function (doc) {
            var region = doc.data();
            if (region.publish !== true) {
                return;
            }
            regionNames[region.id] = region.name;
            regions.push(region);
        });

        regions.sort(function (a, b) {
            return (a.name || '').localeCompare(b.name || '');
        });

        regions.forEach(function (region) {
            $('.region_selector').append($("<option></option>").attr("value", region.id).text(region.name));
        });
    }

    $(document).on('change', '.region_selector', function () {
        selectedRegion = $(this).val() || '';
        $('#storeTable').DataTable().ajax.reload();
    });

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
                const orderableColumns = ['', '', 'title', 'regionId', 'isActive', 'phonenumber', 'createdAt', 'items', 'orders'];
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

                    let filteredRecords = [];

                    await Promise.all(querySnapshot.docs.map(async function (doc) {
                        let childData = doc.data();
                        childData.id = childData.id ? childData.id : doc.id;

                        const phone = childData.phonenumber || '';
                        childData.exportPhone = phone;

                        childData.items = await countIn('vendor_products', childData.id);
                        childData.orders = await countIn('vendor_orders', childData.id);
                        childData.exportDate = formatDate(childData.createdAt);
                        childData.exportRegion = regionNames[childData.regionId] || '';
                        childData.exportStatus = (childData.isActive === false)
                            ? "{{ trans('lang.store_closed') }}" : "{{ trans('lang.store_open') }}";

                        if (selectedRegion !== '' && childData.regionId !== selectedRegion) {
                            return;
                        }

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
                    var activeCount = filteredRecords.filter(function (r) {
                        return r.isActive !== false;
                    }).length;

                    $('.rest_active_count').text(activeCount);
                    $('.rest_inactive_count').text(totalRecords - activeCount);
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
            order: [[6, 'desc']],
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
            /* Rows arrive after the page has loaded, and custom.min.js binds
             * tooltips once on ready - so rows drawn later have none unless they
             * are bound on each draw. */
            drawCallback: function () {
                $('#storeTable [data-toggle="tooltip"]').tooltip();
            },
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

        /* Tooltips read from `title`. Bootstrap 4 is what this panel loads, and it
         * takes the text from there - `data-bs-original-title` is Bootstrap 5's
         * name for an attribute it writes itself, so it said nothing here. */
        var actionHtml = '<span class="action-btn">';

        /* The store the panel is already on cannot be switched to again, so it
         * shows a marker instead of the action. */
        if (id === selectedStoreId) {
            actionHtml += '<a href="javascript:void(0)" class="do_not_delete" data-toggle="tooltip" title="{{ trans('lang.store_active_now') }}"><i class="mdi mdi-check-circle text-success"></i></a>';
        } else {
            actionHtml += '<a href="javascript:void(0)" name="select-btn" class="do_not_delete" dataId="' + id + '" data-toggle="tooltip" title="{{ trans('lang.store_select') }}"><i class="mdi mdi-store"></i></a>';
        }

        actionHtml += '<a href="' + routeView + '" data-toggle="tooltip" title="{{ trans('lang.view') }}"><i class="mdi mdi-eye"></i></a>';
        actionHtml += '<a href="' + routeEdit + '" data-toggle="tooltip" title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';
        actionHtml += '<a id="' + id + '" name="delete-btn" class="do_not_delete" href="javascript:void(0)" data-toggle="tooltip" title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>';
        actionHtml += '</span>';
        html.push(actionHtml);

        var photo = val.photo ? val.photo : placeholderImage;
        html.push('<img alt="" width="100%" style="width:70px;height:70px;" src="' + photo +
            '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="image">' +
            '<a href="' + routeView + '" class="redirecttopage left_space">' + (val.title || '') + '</a>');

        html.push(regionNames[val.regionId] ? regionNames[val.regionId] : '');

        /* The store's own open/closed switch, not the account's approval - the
         * owner's approval applies to all their stores equally, so it would say
         * the same thing on every row. */
        html.push(val.isActive === false
            ? '<span class="badge badge-danger">{{ trans('lang.store_closed') }}</span>'
            : '<span class="badge badge-success">{{ trans('lang.store_open') }}</span>');

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

    /* Switching store repoints the whole panel - Items, Orders, Point Of Sale and
     * the rest all read the selected store - so the page is reloaded rather than
     * patched. */
    $(document).on('click', '[name="select-btn"]', async function () {
        jQuery("#data-table_processing").show();

        var switched = await selectStore(vendorUserId, $(this).attr('dataId'));

        if (!switched) {
            jQuery("#data-table_processing").hide();
            return;
        }

        window.location.reload();
    });

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
