@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.employee_plural') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.employee_plural') }}</li>
                </ol>
            </div>
            <div>
            </div>
        </div>
        <div class="row px-5 mb-2">
            <div class="col-12">
                <span class="font-weight-bold text-danger food-limit-note"></span>
            </div>
        </div>
        <div class="container-fluid page-menu">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs align-items-end card-header-tabs w-100 ">
                                <li class="nav-item active">
                                    <a class="nav-link" href="{!! route('employee.index') !!}"><i class="fa fa-list mr-2"></i>{{ trans('lang.employee_plural') }}</a>
                                </li>
                                <li class="nav-item create-btn">
                                    <a class="nav-link" href="javascript:void(0)"><i class="fa fa-plus mr-2"></i>{{ trans('lang.create_employee') }}</a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body page">
                            {{-- The flex classes sit on the inner wrapper, not on the
                                 element that gets hidden: Bootstrap utilities are
                                 !important, so `.d-flex` beats an inline display:none
                                 and .hide() silently does nothing. --}}
                            <div class="employee-filters mb-3">
                                <div class="d-flex justify-content-end list-filters">
                                    <div class="select-box pl-3 store-filter store-filter-wide" id="store_filter_box">
                                        <select class="form-control store_selector">
                                            <option value="" disabled selected>{{ trans('lang.select_store_filter') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive m-t-10">
                                <table id="employeeTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                    <thead>
                                        <th class="delete-all"><input type="checkbox" class="checkbox_is_check" id="is_active"><label class="col-3 control-label" for="is_active">
                                            <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{ trans('lang.all') }}</a></label>
                                        </th>
                                        <th>{{ trans('lang.user_name') }}</th>
                                        <th>{{ trans('lang.store_info') }}</th>
                                        <th>{{ trans('lang.email') }}</th>
                                        <th>{{ trans('lang.role') }}</th>
                                        <th>{{ trans('lang.active') }}</th>
                                        <th>{{ trans('lang.date') }}</th>
                                        <th>{{ trans('lang.actions') }}</th>
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
    </div>
<!-- Permissions Modal -->
<div class="modal fade" id="permissionsModal" tabindex="-1" role="dialog" aria-labelledby="permissionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="permissionsModalLabel">{{trans('lang.permissions')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="permissionsLoading" class="text-center py-4">
                    <i class="fa fa-spinner fa-spin fa-2x"></i><br>{{trans('lang.loading')}}
                </div>
                <ul id="permissionsList" class="list-group list-group-flush d-none"></ul>
                <div id="noPermissions" class="text-center text-muted py-4 d-none">
                    {{trans('lang.no_active_permissions_assigned')}}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{trans('lang.close')}}</button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
    <script type="text/javascript">
        var database = firebase.firestore();
        var offest = 1;
        var pagesize = 10;
        var end = null;
        var endarray = [];
        var start = null;
        var vendorUserId = "{{ $vendorUserId }}";
        var authRole = "{{ $authRole }}";
        var empVendorId = "{{ $empVendorId }}";
        let currentPermissions = {
            isActive: true   
        };
        var vendorId;
        var ref;
        var append_list = '';
        var placeholderImage = '';
        const roleCache = {};

        /* An employee belongs to one store, so this list covers every store on
         * the account and is narrowed by the two filters. An employee viewing it
         * sees only their own store and gets no filters. */
        var storesById = {};
        var accountStoreIds = [];
        var selectedStore = '';

        ref = database.collection('users').where("role", "==", "employee");

        /* Firestore takes at most ten values in an `in`, so anything queried by
         * store id is asked for ten stores at a time and stitched back together. */
        function chunked(ids) {
            var chunks = [];

            for (var i = 0; i < ids.length; i += 10) {
                chunks.push(ids.slice(i, i + 10));
            }

            return chunks;
        }

        async function loadStores() {
            var snapshots;

            if (authRole === 'employee') {
                if (!empVendorId) {
                    return;
                }
                snapshots = await database.collection('vendors').where('id', '==', empVendorId).get();
            } else {
                snapshots = await database.collection('vendors').where('author', '==', vendorUserId).get();
            }

            snapshots.docs.forEach(function(doc) {
                var store = doc.data();
                var storeId = store.id || doc.id;

                storesById[storeId] = store;
                accountStoreIds.push(storeId);
            });

            /* What the Create tab checks before letting an employee be added. */
            vendorId = accountStoreIds.length ? accountStoreIds[0] : '';
        }

        /* Roles belong to a store, so every store on the account contributes. */
        async function loadAllRoles() {
            try {
                if (!accountStoreIds.length) {
                    return;
                }

                var snapshots = await Promise.all(chunked(accountStoreIds).map(function(chunk) {
                    return database.collection('vendor_employee_roles').where('vendorId', 'in', chunk).get();
                }));

                snapshots.forEach(function(snapshot) {
                    snapshot.forEach(function(doc) {
                        var data = doc.data();
                        roleCache[doc.id] = data.title?.trim() || '—';
                    });
                });
            } catch (e) {
                console.error("Failed to load roles:", e);
            }
        }

        function storeTitle(storeId) {
            return storesById[storeId] ? (storesById[storeId].title || '') : '';
        }

        function fillStoreFilter() {
            var $select = $('.store_selector');
            var stores = accountStoreIds.slice();

            stores.sort(function(a, b) {
                return storeTitle(a).localeCompare(storeTitle(b));
            });

            stores.forEach(function(storeId) {
                $select.append($("<option></option>").attr("value", storeId).text(storeTitle(storeId)));
            });
        }

        document.addEventListener("DOMContentLoaded", async function() {

            await loadStores();
            await loadAllRoles();

            /* An employee belongs to one store and has nothing to choose
             * between. An owner keeps the filter whatever their store count -
             * it is part of the screen, not something that comes and goes. */
            if (authRole === 'employee') {
                $('.employee-filters').hide();
            } else {
                fillStoreFilter();

                $('.store_selector').select2({
                    placeholder: "{{ trans('lang.select_store_filter') }}",
                    dropdownParent: $('#store_filter_box'),
                    minimumResultsForSearch: Infinity,
                    allowClear: true,
                    width: '240px'
                });
            }

            $(document).on('change', '.store_selector', function() {
                selectedStore = $(this).val() || '';
                $('#employeeTable').DataTable().ajax.reload();
            });

            if (authRole === 'employee') {               
                const perm = await getEmployeePermissionForTitle(vendorUserId, "All Employee");
                currentPermissions = {
                    isActive: perm.isActive ?? false
                };

                if (!currentPermissions.isActive) {
                    alert('{{ trans("lang.no_permission") }}');
                    $('#employeeTable').hide();
                    $('.page-menu').html('<p class="text-center text-danger font-weight-bold">{{ trans("lang.no_permission") }}</p>');
                    return;
                }
            }

            $(document.body).on('click', '.redirecttopage', function() {
                var url = $(this).attr('data-url');
                window.location.href = url;
            });
            var placeholder = database.collection('settings').doc('placeHolderImage');
            placeholder.get().then(async function(snapshotsimage) {
                var placeholderImageData = snapshotsimage.data();
                placeholderImage = placeholderImageData.image;
            })
            const table = $('#employeeTable').DataTable({
                pageLength: 10, // Number of rows per page
                processing: false, // Show processing indicator
                serverSide: true, // Enable server-side processing
                responsive: true,
                ajax: async function(data, callback, settings) {
                    const start = data.start;
                    const length = data.length;
                    const searchValue = data.search.value.toLowerCase();
                    const orderColumnIndex = data.order[0].column;
                    const orderDirection = data.order[0].dir;
                    const orderableColumns = ['', 'driverName', 'storeTitle', 'email', 'roleTitle', '', 'createdAt', ''];
                    const orderByField = orderableColumns[orderColumnIndex];
                    if (searchValue.length >= 3 || searchValue.length === 0) {
                        $('#data-table_processing').show();
                    }
                    try {

                        /* One store when the filter names one, otherwise every
                         * store on the account. */
                        var storeIds = selectedStore !== '' ? [selectedStore] : accountStoreIds.slice();

                        var employeeDocs = [];

                        if (storeIds.length) {
                            var snapshots = await Promise.all(chunked(storeIds).map(function(chunk) {
                                return ref.where('vendorID', 'in', chunk).get();
                            }));

                            snapshots.forEach(function(snapshot) {
                                snapshot.docs.forEach(function(doc) {
                                    employeeDocs.push(doc);
                                });
                            });
                        }

                        if (!employeeDocs.length) {
                            $('#data-table_processing').hide(); // Hide loader
                            callback({
                                draw: data.draw,
                                recordsTotal: 0,
                                recordsFiltered: 0,
                                data: [] // No data
                            });
                            return;
                        }
                        let records = [];
                        let filteredRecords = [];
                        await Promise.all(employeeDocs.map(async (doc) => {
                            let childData = doc.data();
                            childData.id = doc.id;
                            childData.driverName = childData.firstName + ' ' + childData.lastName || " "
                            childData.roleTitle = roleCache[childData.employeePermissionId] || '—';
                            childData.storeTitle = storeTitle(childData.vendorID);

                            const options = {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            };
                            var date = '';
                            var time = '';
                            if (childData.hasOwnProperty("createdAt")) {
                                try {
                                    date = childData.createdAt.toDate().toDateString();
                                    time = childData.createdAt.toDate().toLocaleTimeString('en-US');
                                } catch (err) {}
                            }
                            childData.createdDate = date + ' ' + time;


                            if (searchValue) {
                                if (
                                    (childData.driverName && childData.driverName.toString().toLowerCase().includes(searchValue)) ||
                                    (childData.createdDate && childData.createdDate.toString().toLowerCase().indexOf(searchValue) > -1) ||
                                    (childData.email && childData.email.toString().includes(searchValue)) ||
                                    (childData.storeTitle && childData.storeTitle.toString().toLowerCase().includes(searchValue)) ||
                                    (childData.roleTitle && childData.roleTitle.toString().toLowerCase().includes(searchValue))
                                ) {
                                    filteredRecords.push(childData);
                                }
                            } else {
                                filteredRecords.push(childData);
                            }
                        }));
                        filteredRecords.sort((a, b) => {
                            let aValue = a[orderByField] ? a[orderByField].toString().toLowerCase() : '';
                            let bValue = b[orderByField] ? b[orderByField].toString().toLowerCase() : '';
                            if (orderByField === 'createdAt') {
                                aValue = a[orderByField] ? new Date(a[orderByField].toDate()).getTime() : 0;
                                bValue = b[orderByField] ? new Date(b[orderByField].toDate()).getTime() : 0;
                            }
                            if (orderDirection === 'asc') {
                                return (aValue > bValue) ? 1 : -1;
                            } else {
                                return (aValue < bValue) ? 1 : -1;
                            }
                        });
                        const totalRecords = filteredRecords.length;
                        const paginatedRecords = filteredRecords.slice(start, start + length);
                        
                        const formattedRecords = await Promise.all(paginatedRecords.map(async (
                            childData) => {
                            return await buildHTML(childData);
                        }));
                        $('#data-table_processing').hide(); // Hide loader
                        callback({
                            draw: data.draw,
                            recordsTotal: totalRecords,
                            recordsFiltered: totalRecords,
                            data: formattedRecords
                        });
                    } catch (error) {
                        console.error("Error fetching data from Firestore:", error);
                        jQuery('#overlay').hide();
                        callback({
                            draw: data.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            data: []
                        });
                    }
                },
                order: [6, 'desc'],
                columnDefs: [{
                        orderable: false,
                        targets: [0, 4, 6]
                    },

                ],
                /* Rows arrive after the page has loaded, and custom.min.js binds
                 * tooltips once on ready - so rows drawn later have none unless
                 * they are bound on each draw. */
                drawCallback: function() {
                    $('#employeeTable [data-toggle="tooltip"]').tooltip();
                },
                "language": datatableLang,
            });

            function debounce(func, wait) {
                let timeout;
                const context = this;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(context, args), wait);
                };
            }
        });

        async function buildHTML(val) {
            var html = [];
            var id = val.id;
            var route1 = '{{ route('employees.edit', ':id') }}';
            route1 = route1.replace(':id', id);

            html.push('<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open checkbox_is_check right-sign" dataId="' +
                id + '"><label class="col-3 control-label"\n' +
                'for="is_open_' + id + '" ></label></td>');
            
            /* Picture and name in one cell, as Store Info does on the stores
             * list, rather than a column of its own. */
            var photo = val.profilePictureURL ? val.profilePictureURL : placeholderImage;
            html.push('<td><img alt="" width="100%" style="width:70px;height:70px;" src="' + photo +
                '" onerror="this.onerror=null;this.src=&quot;' + placeholderImage + '&quot;" alt="image">' +
                '<a href="' + route1 + '" class="left_space">' + val.driverName + '</a></td>');
            html.push('<td>' + (val.storeTitle || '') + '</td>');
            html.push('<td>' + val.email + '</td>');
            var roleTitle = roleCache[val.employeePermissionId] || '—';
            html.push('<td>' + roleTitle + '</td>');
            if (val.active) {
                html.push('<td><label class="switch"><input type="checkbox" checked id="' + val.id + '" name="isActive"><span class="slider round"></span></label></td>')
            } else {
                html.push('<td><label class="switch"><input type="checkbox"  id="' + val.id + '" name="isActive"><span class="slider round"></span></label></td>')
            }
            html.push('<td>' + val.createdDate + '</td>');            

            var action = '';
            action +=  '<span class="action-btn"><a href="' + route1 + '" data-toggle="tooltip" title="{{ trans('lang.edit') }}"><i class="fa fa-edit"></i></a>';
            var permId = val.employeePermissionId || '';  
            var permId = val.employeePermissionId || '';
            action += '<a href="javascript:void(0)" ' +
                    'class="view-permissions text-info" ' +
                    'data-permission-id="' + permId + '" ' +
                    'data-employee-name="' + (val.driverName || "{{trans('lang.employee')}}") + '" ' +
                    'data-toggle="tooltip" title="{{trans('lang.view_permissions')}}">' +
                    '<i class="mdi mdi-account-check"></i></a>';
           
            action += '<a id="' + val.id + '" class="do_not_delete" name="employee-delete" href="javascript:void(0)" data-toggle="tooltip" title="{{ trans('lang.delete') }}"><i class="fa fa-trash"></i></a>';
            
            action +=  '</span>';
            html.push(action);
            return html;
        }

        $("#is_active").click(function() {
            $("#employeeTable .is_open").prop('checked', $(this).prop('checked'));
        });
        $("#deleteAll").click(function() {
            if ($('#employeeTable .is_open:checked').length) {
                if (confirm('{{trans("lang.are_you_sure_want_to_delete_selected_data")}}')) {
                    jQuery("#data-table_processing").show();
                    $('#employeeTable .is_open:checked').each(async function() {
                        var dataId = $(this).attr('dataId');
                        await deleteDocumentWithImage('users', dataId, ['profilePictureURL']);
                        await deleteDriverData(dataId).then(function() {
                            window.location.reload();
                        });
                    });
                }
            } else {
                alert('{{trans("lang.please_select_any_one_record")}}');
            }
        });

        $(document).on("click", "a[name='employee-delete']", async function(e) {
            var id = this.id;
            await deleteDocumentWithImage('users', id, ['profilePictureURL']);
            await deleteDriverData(id).then(function() {
                window.location.reload();
            });
        });
        async function deleteDriverData(driverId) {
            //delete user from authentication  
            var dataObject = {
                "data": {
                    "uid": driverId
                }
            };
            var projectId = '<?php echo env('FIREBASE_PROJECT_ID'); ?>';
            jQuery.ajax({
                url: 'https://us-central1-' + projectId + '.cloudfunctions.net/deleteUser',
                method: 'POST',
                contentType: "application/json; charset=utf-8",
                data: JSON.stringify(dataObject),
                success: function(data) {
                    console.log('Delete user success:', data.result);
                },
                error: function(xhr, status, error) {
                    var responseText = JSON.parse(xhr.responseText);
                    console.log('Delete user error:', responseText.error);
                }
            });

            //delete user from mysql
            database.collection('settings').doc("Version").get().then(function(snapshot) {
                var settingData = snapshot.data();
                if (settingData && settingData.storeUrl) {
                    var siteurl = settingData.storeUrl + "/api/delete-user";
                    var dataObject = {
                        "uuid": driverId
                    };
                    jQuery.ajax({
                        url: siteurl,
                        method: 'POST',
                        contentType: "application/json; charset=utf-8",
                        data: JSON.stringify(dataObject),
                        success: function(data) {
                            console.log('Delete user from sql success:', data);
                        },
                        error: function(error) {
                            console.log('Delete user from sql error:', error.responseJSON.message);
                        }
                    });
                }
            });
        }
        
       /* Delegates to resolveCurrentStoreId() in layouts/app.blade.php - the one
        * place that knows which store the panel is working on. */
       async function getVendorId(vendorUser) {
           return await resolveCurrentStoreId(vendorUser);
       }
        $(document).on("click", "input[name='isActive']", function(e) {
            var ischeck = $(this).is(':checked');
            var id = this.id;
            if (ischeck) {
                database.collection('users').doc(id).update({
                    'active': true
                }).then(function(result) {
                    jQuery("#data-table_processing").hide();
                });
            } else {
                database.collection('users').doc(id).update({
                    'active': false
                }).then(function(result) {
                    jQuery("#data-table_processing").hide();
                });
            }
        });
        $(document).on("click", ".create-btn", function(e) {
            if (!vendorId) {
                alert("{{trans('lang.no_store_found_create_one_first')}}");
                return;
            }

            window.location.href = "{!! route('employees.create') !!}";
        });
        $(document).on('click', '.view-permissions', async function() {
            const permissionId   = $(this).data('permission-id');
            const employeeName   = $(this).data('employee-name') || "{{trans('lang.employee')}}";

            $('#permissionsModalLabel').text(employeeName + " - {{trans('lang.permissions')}}");

            const list      = $('#permissionsList');
            const loading   = $('#permissionsLoading');
            const noPerms   = $('#noPermissions');

            list.empty().addClass('d-none');
            noPerms.addClass('d-none');
            loading.removeClass('d-none');
            $('#permissionsModal').modal('show');

            if (!permissionId || permissionId.trim() === '') {
                loading.addClass('d-none');
                noPerms.text("{{trans('lang.no_role_permissions_assigned')}}").removeClass('d-none');
                return;
            }

            try {
                const roleDoc = await database.collection('vendor_employee_roles').doc(permissionId).get();

                loading.addClass('d-none');

                if (!roleDoc.exists) {
                    noPerms.text("{{trans('lang.role_not_found')}}").removeClass('d-none');
                    return;
                }

                const roleData     = roleDoc.data();
                const permissions  = roleData.permissions || [];

                const activeTitles = permissions
                    .filter(perm => perm?.isActive === true && perm?.title)
                    .map(perm => perm.title.trim());

                if (activeTitles.length === 0) {
                    noPerms.removeClass('d-none');
                    return;
                }

                list.html('<p class="mb-0">' + activeTitles.join(', ') + '</p>');
                list.removeClass('d-none');

            } catch (err) {
                console.error("Error loading permissions:", err);
                loading.addClass('d-none');
                noPerms.text("{{trans('lang.failed_to_load_permissions')}}").removeClass('d-none');
            }
        });

    </script>
@endsection
