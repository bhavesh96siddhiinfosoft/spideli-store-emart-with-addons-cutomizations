@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor orderTitle">{{trans('lang.pos_orders')}} </h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.pos_orders')}}</li>
            </ol>
        </div>
        <div>
        </div>
    </div>

    <div class="container-fluid">
        <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">
            {{trans('lang.processing')}}...
        </div>

        <div class="row">
            <div class="col-12">

                {{-- The period picker and print button --}}
                <div class="card border mb-3" id="order_period_card">
                    <div class="card-body p-3">
                        <div class="d-flex flex-wrap align-items-center">
                            <label class="mb-0 mr-2 font-weight-bold" for="order_period">
                                <i class="mdi mdi-calendar-clock mr-1"></i>{{trans('lang.order_history_period')}}
                            </label>
                            <select id="order_period" class="form-control w-auto mr-2 mb-0">
                                <option value="all">{{trans('lang.order_history_period_all')}}</option>
                            </select>
                            <div id="order_period_custom" class="d-flex flex-wrap align-items-center mr-2" style="display:none;">
                                <input type="date" id="order_period_from" class="form-control w-auto mr-2 mb-0">
                                <span class="mr-2">&ndash;</span>
                                <input type="date" id="order_period_to" class="form-control w-auto mr-2 mb-0">
                                <button type="button" id="order_period_apply" class="btn btn-primary btn-sm mr-2">{{trans('lang.order_history_period_apply')}}</button>
                            </div>
                            <span id="order_period_summary" class="text-muted small ml-auto mr-2"></span>
                            <button type="button" id="order_history_print" class="btn btn-outline-primary btn-sm">
                                <i class="mdi mdi-printer mr-1"></i>{{trans('lang.order_history_print')}}
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Shown only on paper when printed --}}
                <div class="col-12 order-print-only mb-3" id="order_history_print_header" style="display:none;">
                    <h3 class="mb-1">{{ trans('lang.order_history_print_title') }} ({{ trans('lang.pos_orders') }})</h3>
                    <p class="mb-0 small"><strong>{{ trans('lang.store') }}:</strong> <span id="print_store"></span></p>
                    <p class="mb-0 small"><strong>{{ trans('lang.order_history_print_period') }}:</strong> <span id="print_period"></span></p>
                    <p class="mb-0 small"><strong>{{ trans('lang.total_orders') }}:</strong> <span id="print_total_orders"></span> | <strong>{{ trans('lang.total_amount') }}:</strong> <span id="print_total_amount"></span></p>
                    <p class="mb-2 small"><strong>{{ trans('lang.order_history_print_generated') }}:</strong> <span id="print_generated"></span></p>
                    <hr class="mt-2 mb-2">
                </div>

                <div class="card">
                    <div class="card-body">

                        <div class="table-responsive m-t-10">
                            <table id="orderTable"
                                class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active">
                                                <a id="deleteAll" class="do_not_delete" href="javascript:void(0)">
                                                    <i class="mdi mdi-delete"></i> {{ trans('lang.all') }}</a></label>
                                        </th>
                                        <th>{{trans('lang.order_id')}}</th>                                       
                                        <th>{{trans('lang.order_user_id')}}</th>                                        
                                        <th>{{trans('lang.date')}}</th>
                                        <th>{{trans('lang.amount')}}</th>
                                        <th>{{trans('lang.order_order_status_id')}}</th>
                                        <th>{{trans('lang.actions')}}</th>
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

@endsection

@section('scripts')

<script type="text/javascript">

    var database = firebase.firestore();

    var redData = ref;
    var currentCurrency = '';
    var currencyAtRight = false;
    var decimal_degits = 0;
    var vendorUserId = "{{ $vendorUserId }}";
    var refCurrency = storeCurrencyRef();
    refCurrency.get().then(async function (snapshots) {
        var currencyData = snapshots.docs[0].data();
        currentCurrency = currencyData.symbol;
        currencyAtRight = currencyData.symbolAtRight;
        if (currencyData.decimal_degits) {
            decimal_degits = currencyData.decimal_degits;
        }
    });

    var order_status = jQuery('#order_status').val();
    var search = jQuery("#search").val();

    var refData = database.collection('vendor_orders').where('isPosOrder','==',true).where('vendor.author', "==", vendorUserId);

    var ref = '';
  

    $(document.body).on('change', '#order_status', function () {
        order_status = jQuery(this).val();
    });

    $(document.body).on('keyup', '#search', function () {
        search = jQuery(this).val();
    });

    var orderStatus = '<?php if (isset($_GET['status'])) {
        echo $_GET['status'];
    } else {
        echo '';
    } ?>';          


    if (orderStatus) {

        ref = refData.orderBy('createdAt', 'desc').where('status', '==', orderStatus);


    } else {

        if ((order_status == 'All' || order_status != '') && search != '') {

            ref = refData;
        } else {
            ref = refData.orderBy('createdAt', 'desc');
        }
    }

    var orderPeriodFrom = null;
    var orderPeriodTo = null;
    var orderPeriodLabel = '';
    var periodsPopulated = false;
    var currentStoreTitle = '';
    var totalFilteredAmount = 0;

    function populateOrderPeriods(snapshots) {
        if (periodsPopulated) return;
        var seen = {};
        var months = [];
        snapshots.docs.forEach(function (doc) {
            var created = doc.data().createdAt;
            if (!created || typeof created.toDate !== 'function') {
                return;
            }
            var date = created.toDate();
            var key = date.getFullYear() + '-' + ('0' + (date.getMonth() + 1)).slice(-2);
            if (!seen[key]) {
                seen[key] = true;
                months.push({
                    key: key,
                    label: date.toLocaleString('en-US', { month: 'long', year: 'numeric' })
                });
            }
        });
        if (months.length === 0) return;
        months.sort(function (a, b) { return a.key < b.key ? 1 : -1; });

        var select = $('#order_period');
        select.find('option:not([value="all"])').remove();
        months.forEach(function (month) {
            select.append($('<option>').val('month:' + month.key).text(month.label));
        });
        select.append($('<option>').val('custom').text("{{ trans('lang.order_history_period_custom') }}"));
        periodsPopulated = true;
    }

    function withinOrderPeriod(order) {
        if (!orderPeriodFrom && !orderPeriodTo) {
            return true;
        }
        if (!order.createdAt || typeof order.createdAt.toDate !== 'function') {
            return true;
        }
        var date = order.createdAt.toDate();
        if (orderPeriodFrom && date < orderPeriodFrom) { return false; }
        if (orderPeriodTo && date > orderPeriodTo) { return false; }
        return true;
    }

    function setOrderPeriodSummary(label) {
        orderPeriodLabel = label || '';
        $('#order_period_summary').text(label
            ? "{{ trans('lang.order_history_period_showing') }}".replace(':period', label)
            : '');
    }

    document.addEventListener('DOMContentLoaded', async function() {   
        
        jQuery('#search').hide();  

        $(document.body).on('click', '.redirecttopage', function () {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });

        $(document.body).on('change', '#selected_search', function () {

            if (jQuery(this).val() == 'status') {
                jQuery('#order_status').show();
                jQuery('#search').hide();
            } else {

                jQuery('#order_status').hide();
                jQuery('#search').show();

            }
        });

        jQuery("#data-table_processing").show();

        const table = $('#orderTable').DataTable({
            pageLength: 10,
            processing: false,
            serverSide: true,
            responsive: true,
            ajax: async function (data, callback, settings) {
                const start = data.start;
                const length = data.length;
                const searchValue = data.search.value.toLowerCase();
                const orderColumnIndex = data.order[0].column;
                const orderDirection = data.order[0].dir;

                const orderableColumns =  
                 ['', 'id',  'user', 'createdAt', 'amount', 'status', ''];

                const orderByField = orderableColumns[orderColumnIndex];

                if (searchValue.length >= 3 || searchValue.length === 0) {
                    $('#data-table_processing').show();
                }

                try {
                    const querySnapshot = await ref.get();
                    populateOrderPeriods(querySnapshot);
                    if (querySnapshot.empty) {
                        $('#data-table_processing').hide();
                        callback({
                            draw: data.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            data: []
                        });
                        return;
                    }

                    let records = [];
                    let filteredRecords = [];
                    totalFilteredAmount = 0;

                    await Promise.all(querySnapshot.docs.map(async (doc) => {
                        let childData = doc.data();
                        childData.id = doc.id;

                        if (!withinOrderPeriod(childData)) {
                            return;
                        }
                       
                        if (childData.userID) {
                            var user = await getuserName(childData.authorID);
                            childData['user'] = user || "";
                        } else {
                            childData['user'] = "";
                        }

                        childData.amount = await buildHTMLProductstotal(childData);
                        var rawAmount = parseFloat(String(childData.amount).replace(/[^0-9.]/g, '')) || 0;
                        totalFilteredAmount += rawAmount;

                        if (searchValue) {
                            var date = '';
                            var time = '';
                            if (childData.hasOwnProperty("createdAt")) {
                                try {
                                    date = childData.createdAt.toDate().toDateString();
                                    time = childData.createdAt.toDate().toLocaleTimeString('en-US');
                                } catch (err) {
                                }
                            }
                            var createdAt = date + ' ' + time;
                            if (
                                (user && user.toLowerCase().includes(searchValue))  ||
                                (childData.id && childData.id.toLowerCase().includes(searchValue)) ||
                                (childData.status && childData.status.toLowerCase().includes(searchValue)) || (createdAt && createdAt.toString().toLowerCase().indexOf(searchValue) > -1)
                            ) {
                                filteredRecords.push(childData);
                            }
                        } else {
                            filteredRecords.push(childData);
                        }
                    }));

                    filteredRecords.sort((a, b) => {
                        let aValue = a[orderByField] ? a[orderByField].toString().toLowerCase().trim() : '';
                        let bValue = b[orderByField] ? b[orderByField].toString().toLowerCase().trim() : '';
                        if (orderByField === 'createdAt') {
                            try {
                                aValue = a[orderByField] ? new Date(a[orderByField].toDate()).getTime() : 0;
                                bValue = b[orderByField] ? new Date(b[orderByField].toDate()).getTime() : 0;
                            } catch (err) {
                            }
                        }
                        if (orderByField === 'amount') {
                            aValue = a[orderByField].slice(1) ? parseInt(a[orderByField].slice(1)) : 0;
                            bValue = b[orderByField].slice(1) ? parseInt(b[orderByField].slice(1)) : 0;
                        }
                        if (orderDirection === 'asc') {
                            return (aValue > bValue) ? 1 : -1;
                        } else {
                            return (aValue < bValue) ? 1 : -1;
                        }
                    });

                    const totalRecords = filteredRecords.length;
                    const paginatedRecords = (length === -1) ? filteredRecords : filteredRecords.slice(start, start + length);

                    const formattedRecords = await Promise.all(paginatedRecords.map(async (childData) => {
                        return await buildHTML(childData);
                    }));

                    $('#data-table_processing').hide();
                    callback({
                        draw: data.draw,
                        recordsTotal: totalRecords,
                        recordsFiltered: totalRecords,
                        data: formattedRecords
                    });

                } catch (error) {
                    console.error("Error fetching data from Firestore:", error);
                    $('#data-table_processing').hide();
                    callback({
                        draw: data.draw,
                        recordsTotal: 0,
                        recordsFiltered: 0,
                        data: []
                    });
                }
            },
           
            order:  [3, 'desc'] ,
            columnDefs: [
                {
                   
                    targets:  [3],
                    type: 'date',
                    render: function (data) {
                        return data; // Adjust formatting if needed
                    }
                },
                {
                    orderable: false,                   
                    targets:  [0,6] 
                }
            ],
            "language": datatableLang,
        });

        if (typeof authRole !== 'undefined' && authRole === 'employee' && typeof empVendorId !== 'undefined') {
            database.collection('vendors').doc(empVendorId).get().then(function(s) {
                if (s.exists && s.data().title) {
                    currentStoreTitle = s.data().title;
                }
            });
        } else if (typeof user_id !== 'undefined') {
            if (typeof resolveCurrentStoreId === 'function') {
                resolveCurrentStoreId(user_id).then(function(storeId) {
                    database.collection('vendors').doc(storeId).get().then(function(s) {
                        if (s.exists && s.data().title) {
                            currentStoreTitle = s.data().title;
                        }
                    });
                });
            }
        }

        $(document).on('change', '#order_period', function () {
            var value = $(this).val();
            $('#order_period_custom').toggle(value === 'custom');

            if (value === 'custom') {
                return;
            }

            if (value === 'all') {
                orderPeriodFrom = null;
                orderPeriodTo = null;
                setOrderPeriodSummary('');
            } else {
                var parts = value.replace('month:', '').split('-');
                var year = parseInt(parts[0], 10);
                var month = parseInt(parts[1], 10) - 1;
                orderPeriodFrom = new Date(year, month, 1, 0, 0, 0, 0);
                orderPeriodTo = new Date(year, month + 1, 0, 23, 59, 59, 999);
                setOrderPeriodSummary($(this).find('option:selected').text());
            }
            table.draw();
        });

        $(document).on('click', '#order_period_apply', function () {
            var from = $('#order_period_from').val();
            var to = $('#order_period_to').val();
            if (!from && !to) {
                alert("{{ trans('lang.order_history_period_pick_dates') }}");
                return;
            }
            orderPeriodFrom = from ? new Date(from + 'T00:00:00') : null;
            orderPeriodTo = to ? new Date(to + 'T23:59:59') : null;
            if (orderPeriodFrom && orderPeriodTo && orderPeriodFrom > orderPeriodTo) {
                alert("{{ trans('lang.order_history_period_bad_range') }}");
                return;
            }
            setOrderPeriodSummary([from, to].filter(Boolean).join(' - '));
            table.draw();
        });

        $(document).on('click', '#order_history_print', function () {
            var storeHeading = $('.orderTitle').text().replace(/POS Orders\s*-\s*/i, '').trim();
            $('#print_store').text(currentStoreTitle || storeHeading || 'Store');
            $('#print_period').text(orderPeriodLabel !== ''
                ? orderPeriodLabel
                : "{{ trans('lang.order_history_period_all') }}");

            var now = new Date();
            $('#print_generated').text(now.toDateString() + ' ' + now.toLocaleTimeString());
            $('#print_total_orders').text(table.rows().count() || '0');

            var formattedTotal = currencyAtRight 
                ? totalFilteredAmount.toFixed(decimal_degits) + '' + currentCurrency 
                : currentCurrency + '' + totalFilteredAmount.toFixed(decimal_degits);
            $('#print_total_amount').text(formattedTotal);

            var currentLen = table.page.len();
            table.page.len(-1).draw();
            setTimeout(function () {
                window.print();
                table.page.len(currentLen).draw();
            }, 500);
        });

        function debounce(func, wait) {
            let timeout;
            const context = this;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }
        $('#search-input').on('input', debounce(function () {
            const searchValue = $(this).val();
            if (searchValue.length >= 3) {
                $('#data-table_processing').show();
                table.search(searchValue).draw();
            } else if (searchValue.length === 0) {
                $('#data-table_processing').show();
                table.search('').draw();
            }
        }, 300));

    });

    async function buildHTML(val) {
        var html = [];
        newdate = '';
        var id = val.id;
        var vendorID = val.vendorID;

        var route1 = '{{route("orders.edit",":id")}}';
        route1 = route1.replace(':id', id);

        var printRoute = '{{route("vendors.orderprint",":id")}}';
        printRoute = printRoute.replace(':id', id);

       
       
        html.push('<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' +
                'for="is_open_' + id + '" ></label></td>');
       
        html.push('<a data-url="' + route1 + '" href="'+route1+'" class="redirecttopage">' + val.id + '</a>');

       
        if (val.hasOwnProperty("authorID") && val.authorID) {
            var user = await getuserName(val.authorID);

            if (user) {
                html.push('<a  data-url="javascript:void(0)" href="javascript:void(0)" class="redirecttopage">' + user + '</a>');
            } else {
                html.push('<td>{{trans("lang.unknown")}}</td>');
            }

        } else {
            html.push('<td></td>');
        }

        


        var date = '';
        var time = '';
        if (val.hasOwnProperty("createdAt")) {

            try {
                date = val.createdAt.toDate().toDateString();
                time = val.createdAt.toDate().toLocaleTimeString('en-US');
            } catch (err) {

            }
            html.push('<td class="dt-time">' + date + ' ' + time + '</td>');
        } else {
            html.push('<td></td>');
        }
        var price = 0;


        var price = await buildHTMLProductstotal(val);
        html.push('<span class="text-green">' + price + '</span>');


        if (val.status == 'InProcess') {
            html.push('<span class="order_placed"><span>' + val.status + '</span></span>');
        } else if (val.status == 'InTransit') {
            html.push('<span class="in_transit"><span>' + val.status + '</span></span>');
        } else if (val.status == 'Delivered') {
            html.push('<span class="order_completed"><span>' + val.status + '</span></span>');
        } else {
            html.push('<span class="order_completed"><span>' + val.status + '</span></span>');
        }
        var actionHtml = '';
        actionHtml = actionHtml + '<span class="action-btn">';
        actionHtml = actionHtml + '<a href="' + route1 + '"><i class="fa fa-eye"></i></a>';
       
        actionHtml = actionHtml + '<a href="' + printRoute + '"><i class="fa fa-print" style="font-size:20px;"></i></a>';       

        
        actionHtml = actionHtml + '<a id="' + val.id + '" class="delete-btn" name="order-delete" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
        
        actionHtml = actionHtml + '</span>';
        html.push(actionHtml);
        return html;
    }

    $("#is_active").click(function () {
        $("#orderTable .is_open").prop('checked', $(this).prop('checked'));

    });

    async function getuserName(id) {
        var name = '';
        await database.collection('users').where("id", "==", id).get().then(async function (snapshotsorder) {

            if (snapshotsorder.docs.length) {
                var user = snapshotsorder.docs[0].data();
                name = user.firstName + ' ' + user.lastName;
            }
        });
        return name;
    }

    $("#deleteAll").click(function () {

        if ($('#orderTable .is_open:checked').length) {

            if (confirm("{{trans('lang.are_you_sure_want_to_delete_selected_data')}}")) {
                jQuery("#data-table_processing").show();
                $('#orderTable .is_open:checked').each(function () {
                    var dataId = $(this).attr('dataId');

                    database.collection('restaurant_orders').doc(dataId).delete().then(function () {

                        setTimeout(function () {
                            window.location.reload();
                        }, 7000);

                    });

                });

            }
        } else {
            alert("{{trans('lang.please_select_any_one_record')}}");
        }
    });

    $(document).on("click", "a[name='order-delete']", function (e) {
        if (confirm("{{trans('lang.are_you_sure_want_to_delete_selected_data')}}")) {
            var id = this.id;
            database.collection('restaurant_orders').doc(id).delete().then(function (result) {
                window.location.href = '{{ url()->current() }}';
            });
        }
    });


    async function getStoreNameFunction(vendorId) {
        var vendorName = '';
        await database.collection('vendors').where('id', '==', vendorId).get().then(async function (snapshots) {
            if (!snapshots.empty) {
                var vendorData = snapshots.docs[0].data();

                vendorName = vendorData.title;
                $('.orderTitle').html('{{trans("lang.pos_orders")}} - ' + vendorName);

                if (vendorData.dine_in_active == true) {
                    $(".dine_in_future").show();
                }              

            }
        });

        return vendorName;

    }


    async function getUserNameFunction(userId) {
        var userName = '';
        await database.collection('users').where('id', '==', userId).get().then(async function (snapshots) {
            var user = snapshots.docs[0].data();

            userName = user.name;
            $('.orderTitle').html('{{trans("lang.pos_orders")}} - ' + userName + "(" + user.role + ")");
        });

        return userName;

    }
    function buildHTMLProductstotal(snapshotsProducts) {
        let order_subtotal = 0;
        let total_discount = 0;
        let total_tax_amount = 0;
        let tip_amount = parseFloat(snapshotsProducts.tip_amount || 0);
        let deliveryCharge = parseFloat(snapshotsProducts.deliveryCharge || 0);
        let platformFee = parseFloat(snapshotsProducts.platformFee || 0);
        let packagingCharge = parseFloat(snapshotsProducts.vendor.packagingCharge || 0);
        let packagingChargeEnable = snapshotsProducts.packagingChargeEnable;

        //  Calculate subtotal and product extras
        for (let i = 0; i < snapshotsProducts.products.length; i++) {
            let product = snapshotsProducts.products[i];
            let basePrice = (product.discountPrice && parseFloat(product.discountPrice) > 0) ? parseFloat(product.discountPrice) : parseFloat(product.price);
            let itemGross = (basePrice + parseFloat(product.extras_price || 0)) * parseInt(product.quantity);
            order_subtotal += itemGross;
        }

        // Total discounts
        let order_discount = parseFloat(snapshotsProducts.discount || 0);
        let special_discount = parseFloat(snapshotsProducts.specialDiscount?.special_discount || 0);
            total_discount = order_discount + special_discount;

        // Calculate item-level taxes (if product-level)
        if (snapshotsProducts.taxScope === "product") {
            let itemSubtotal = order_subtotal;
            snapshotsProducts.products.forEach(product => {
                let basePrice = (product.discountPrice && parseFloat(product.discountPrice) > 0) ? parseFloat(product.discountPrice) : parseFloat(product.price);
                let itemGross = (basePrice + parseFloat(product.extras_price || 0)) * parseInt(product.quantity);
                let itemDiscount = (itemSubtotal > 0) ? (itemGross / itemSubtotal) * total_discount : 0;
                let itemTaxable = Math.max(0, itemGross - itemDiscount);
                let itemTaxes = product.taxSetting || [];
                itemTaxes.forEach(tax => {
                    if (tax.enable) {
                        let taxAmount = 0;
                        if (tax.type === "percentage") {
                            taxAmount = (tax.tax / 100) * itemTaxable;
                        } else {
                            taxAmount = tax.tax;
                        }
                        total_tax_amount += parseFloat(taxAmount);
                    }
                });
            });
        } 

        // Order-level taxes (if order-level)
        if (snapshotsProducts.taxScope === "order") {
            let orderTaxable = Math.max(0, order_subtotal - total_discount);
            (snapshotsProducts.taxSetting || []).forEach(tax => {
                if (tax.enable) {
                    let taxAmount = 0;
                    if (tax.type === "percentage") {
                        taxAmount = (tax.tax / 100) * orderTaxable;
                    } else {
                        taxAmount = tax.tax;
                    }
                    total_tax_amount += parseFloat(taxAmount);
                }
            });
        }

        // Delivery, packaging, platform taxes
        if(packagingChargeEnable){
            let extraCharges = [
                {amount: packagingCharge, taxes: snapshotsProducts.packagingTax || []},
            ];

            extraCharges.forEach(scope => {
                scope.taxes?.forEach(tax => {
                    if (tax.enable) {
                        let taxAmount = 0;
                        if (tax.type === "percentage") {
                            taxAmount = (tax.tax / 100) * scope.amount;
                        } else {
                            taxAmount = tax.tax;
                        }
                        total_tax_amount += parseFloat(taxAmount);
                    }
                });
            });
        }

        //Final subtotal after discounts
        order_subtotal = order_subtotal - total_discount;

        // Final total
        let order_total = order_subtotal + (packagingChargeEnable ? packagingCharge : 0) + total_tax_amount;

        if (currencyAtRight) {
            order_total_val = parseFloat(order_total).toFixed(decimal_degits) + '' + currentCurrency;
        } else {
            order_total_val = currentCurrency + '' + parseFloat(order_total).toFixed(decimal_degits);
        }

        return order_total_val;
    }

</script>

@endsection

<style>
    .order-print-only { display: none; }

    @media print {
        header, nav, footer, .left-sidebar, .topbar, .navbar, .page-titles,
        .breadcrumb, #order_period_card, .admin-top-section,
        .dataTables_length, .dataTables_filter, .dt-buttons,
        .dataTables_info, .dataTables_paginate, #data-table_processing,
        .card-header, .action-btn, .delete-all,
        th:first-child, td:first-child,
        th:last-child, td:last-child,
        .sidebar-footer { display: none !important; }

        .order-print-only { display: block !important; }

        .page-wrapper { margin-left: 0 !important; padding: 0 !important; }
        .container-fluid { padding: 0 !important; }
        .card, .card-body, .table-list, .table-responsive {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            overflow: visible !important;
        }

        body {
            background: #fff !important;
            font-size: 10pt;
            color: #000 !important;
        }

        table#orderTable {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        table#orderTable th, table#orderTable td {
            border: 1px solid #ccc !important;
            padding: 6px 8px !important;
        }

        table#orderTable tr {
            page-break-inside: avoid;
        }

        .order_placed, .in_transit, .order_completed {
            color: #000 !important;
        }

        a {
            text-decoration: none !important;
            color: #000 !important;
        }
    }
</style>
