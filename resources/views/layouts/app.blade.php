<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" <?php if (str_replace('_', '-', app()->getLocale()) == 'ar' || @$_COOKIE['is_rtl'] == 'true') { ?> dir="rtl" <?php } ?>>

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/x-icon" href="{{ asset('images/spideli-circle.png') }}">
        <!-- Fonts -->
        <link rel="dns-prefetch" href="//fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/css/bootstrap-timepicker.min.css">

        <link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
        <?php if (str_replace('_', '-', app()->getLocale()) == 'ar' || @$_COOKIE['is_rtl'] == 'true') { ?>
        <link href="{{ asset('assets/plugins/bootstrap/css/bootstrap-rtl.min.css') }}" rel="stylesheet">
        <?php } ?>
        <link href="{{ asset('css/style.css') }}" rel="stylesheet">
        <?php if (str_replace('_', '-', app()->getLocale()) == 'ar' || @$_COOKIE['is_rtl'] == 'true') { ?>
        <link href="{{ asset('css/style_rtl.css') }}" rel="stylesheet">
        <?php } ?>
        <link href="{{ asset('css/icons/font-awesome/css/font-awesome.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/plugins/toast-master/css/jquery.toast.css') }}" rel="stylesheet">
        <link href="{{ asset('css/colors/blue.css') }}" rel="stylesheet">
        <link href="{{ asset('css/chosen.css') }}" rel="stylesheet">
        <link href="{{ asset('css/bootstrap-tagsinput.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/plugins/select2/dist/css/select2.min.css') }}" rel="stylesheet">

        <link href="https://fonts.googleapis.com/css2?family=Urbanist:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

        <!-- Datatable css -->

        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
        <link href="{{ asset('css/toastr.min.css')}}" rel="stylesheet">
        <!-- @yield('style') -->
        <?php if (isset($_COOKIE['store_panel_color'])) { ?>
        <style type="text/css">
            .topbar {
                background: <?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            .sidebar-nav ul li a {
                border-bottom: <?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            .sidebar-nav ul li a:hover i {
                color: <?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            .vendor_payout_create-inner fieldset legend {
                background: <?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            a {
                color: <?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            a:hover,
            a:focus {
                color: <?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            a.link:hover,
            a.link:focus {
                color: <?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            html body blockquote {
                border-left: 5px solid<?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            .text-warning {
                color: <?php echo $_COOKIE['store_panel_color'];
                ?> !important;
            }

            .text-info {
                color: <?php echo $_COOKIE['store_panel_color'];
                ?> !important;
            }

            .sidebar-nav ul li a:hover {
                color: <?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            .btn-primary {
                background: <?php echo $_COOKIE['store_panel_color'];
                ?>;
                border: 1px solid<?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            .sidebar-nav>ul>li.active>a {
                color: <?php echo $_COOKIE['store_panel_color'];
                ?>;
                border-left: 3px solid<?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            .sidebar-nav>ul>li.active>a i {
                color: <?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            .bg-info {
                background-color: <?php echo $_COOKIE['store_panel_color'];
                ?> !important;
            }

            .bellow-text ul li>span {
                color: <?php echo $_COOKIE['store_panel_color'];
                ?>
            }

            .table tr td.redirecttopage {
                color: <?php echo $_COOKIE['store_panel_color'];
                ?>
            }

            ul.rating {
                color: <?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            nav-link.active {
                background-color: <?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            .nav-tabs.card-header-tabs .nav-link:hover {
                background: <?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            .nav-tabs .nav-item.show .nav-link,
            .nav-tabs .nav-link.active {
                color: #fff;
                background: <?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            .btn-warning,
            .btn-warning.disabled {
                background: <?php echo $_COOKIE['store_panel_color'];
                ?>;
                border: 1px solid<?php echo $_COOKIE['store_panel_color'];
                ?>;
                box-shadow: none;
            }

            .payment-top-tab .nav-tabs.card-header-tabs .nav-link.active,
            .payment-top-tab .nav-tabs.card-header-tabs .nav-link:hover {
                border-color: <?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            .nav-tabs.card-header-tabs .nav-link span.badge-success {
                background: <?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            .nav-tabs.card-header-tabs .nav-link.active span.badge-success,
            .nav-tabs.card-header-tabs .nav-link:hover span.badge-success,
            .sidebar-nav ul li a.active,
            .sidebar-nav ul li a.active:hover,
            .sidebar-nav ul li.active a.has-arrow:hover,
            .topbar ul.dropdown-user li a:hover {
                color: <?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            .sidebar-nav ul li a.has-arrow:hover::after,
            .sidebar-nav .active>.has-arrow::after,
            .sidebar-nav li>.has-arrow.active::after,
            .sidebar-nav .has-arrow[aria-expanded="true"]::after,
            .sidebar-nav ul li a:hover {
                border-color: <?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            [type="checkbox"]:checked+label::before {
                border-right: 2px solid<?php echo $_COOKIE['store_panel_color'];
                ?>;
                border-bottom: 2px solid<?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            .btn-primary:hover,
            .btn-primary.disabled:hover {
                background: <?php echo $_COOKIE['store_panel_color'];
                ?>;
                border: 1px solid<?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            .btn-primary.active,
            .btn-primary:active,
            .btn-primary:focus,
            .btn-primary.disabled.active,
            .btn-primary.disabled:active,
            .btn-primary.disabled:focus,
            .btn-primary.active.focus,
            .btn-primary.active:focus,
            .btn-primary.active:hover,
            .btn-primary.focus:active,
            .btn-primary:active:focus,
            .btn-primary:active:hover,
            .open>.dropdown-toggle.btn-primary.focus,
            .open>.dropdown-toggle.btn-primary:focus,
            .open>.dropdown-toggle.btn-primary:hover,
            .btn-primary.focus,
            .btn-primary:focus,
            .btn-primary:not(:disabled):not(.disabled).active:focus,
            .btn-primary:not(:disabled):not(.disabled):active:focus,
            .show>.btn-primary.dropdown-toggle:focus,
            .btn-warning:hover,
            .btn-warning:hover,
            .btn-warning.disabled:hover,
            .btn-warning.active.focus,
            .btn-warning.active:focus,
            .btn-warning.active:hover,
            .btn-warning.focus:active,
            .btn-warning:active:focus,
            .btn-warning:active:hover,
            .open>.dropdown-toggle.btn-warning.focus,
            .open>.dropdown-toggle.btn-warning:focus,
            .open>.dropdown-toggle.btn-warning:hover,
            .btn-warning.focus,
            .btn-warning:focus {
                background: <?php echo $_COOKIE['store_panel_color'];
                ?>;
                border-color: <?php echo $_COOKIE['store_panel_color'];
                ?>;
                box-shadow: 0 0 0 0.2rem<?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            .language-options select option,
            .pagination>li>a.page-link:hover {
                background: <?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            .nav-tabs.card-header-tabs .active.nav-item .nav-link {
                background: <?php echo $_COOKIE['store_panel_color'];
                ?>;
            }

            .print-btn button {
                border: 2px solid<?php echo $_COOKIE['store_panel_color'];
                ?>;
                color: <?php echo $_COOKIE['store_panel_color'];
                ?>;
            }
            .review-rating-demo{background: <?php echo $_COOKIE['store_panel_color'];
                ?>;}
                .restaurant_payout_create-inner fieldset legend {
                background: <?php echo $_COOKIE['store_panel_color'];
                ?>;
            }
            .table tr td > span.badge {
    display: inline-block;
    padding: 10px 15px 10px 15px;
    line-height: 1;
    text-transform: capitalize;
    border-radius: 50px;
    text-align: center;
    font-size: 13px;
}
        </style>
        <?php } ?>

        <?php $id = Auth::user()->getvendorId(); ?>
        <script type="text/javascript">
            var cuser_id = '<?php echo $id; ?>';
        </script>

    </head>

    <body>

        <div id="app" class="fix-header fix-sidebar card-no-border">

            <div id="main-wrapper">
                <div id="data-table_processing" class="page-overlay" style="display:none;">
                    <div class="overlay-text">
                        <img src="{{ asset('images/spinner.gif') }}">
                    </div>
                </div>

                <header class="topbar">

                    <nav class="navbar top-navbar navbar-expand-md navbar-light">
                        @include('layouts.header')
                    </nav>

                </header>

                <aside class="left-sidebar">
                    <!-- Sidebar scroll-->
                    <div class="scroll-sidebar">
                        @include('layouts.menu')
                    </div>
                    <!-- End Sidebar scroll-->
                </aside>
            </div>

            <main class="py-4">
                @yield('content')
            </main>
            <div class="modal fade" id="notification_order" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered notification-main" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title order_subject" id="exampleModalLongTitle"></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <h6><span id="auth_accept_name" class="order_message"></span></h6>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary"><a href="{{ route('orders') }}" id="notification_url">{{trans('lang.go')}}</a></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="notification_book_table_order" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered notification-main" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title dinein_order_subject" id="exampleModalLongTitle"></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <h6><span id="auth_accept_name_book_table" class="dinein_order_msg"></span></h6>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary"><a href="{{ route('booktable') }}" id="notification_book_table_url">{{trans('lang.go')}}</a>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="notification_accepted_order" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered notification-main" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title driver_accepted_subject" id="exampleModalLongTitle"></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <h6><span id="np_accept_name" class="driver_accepted_msg"></span></h6>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary"><a href="{{ route('orders') }}" id="notification_accepted_a">{{trans('lang.go')}}</a></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="advertisement_accepted_notification" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered notification-main" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title advertisement_accepted_sub" id="exampleModalLongTitle"></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <h6><span id="advertisement_accepted_msg" class="advertisement_accepted_msg"></span></h6>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary"><a href="{{ route('advertisements') }}" id="advertisement_accepted_route">{{trans('lang.go')}}</a></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="advertisement_canceled_notification" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered notification-main" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title advertisement_cancelled_sub" id="exampleModalLongTitle"></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <h6><span id="advertisement_cancelled_msg" class="advertisement_cancelled_msg"></span></h6>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary"><a href="{{ route('advertisements') }}" id="advertisement_canceled_route">{{trans('lang.go')}}</a></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="advertisement_paused_notification" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered notification-main" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title advertisement_paused_sub" id="exampleModalLongTitle"></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <h6><span id="advertisement_paused_msg" class="advertisement_paused_msg"></span></h6>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary"><a href="{{ route('advertisements') }}" id="advertisement_paused_route">{{trans('lang.go')}}</a></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="advertisement_resumed_notification" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered notification-main" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title advertisement_resumed_sub" id="exampleModalLongTitle"></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <h6><span id="advertisement_resumed_msg" class="advertisement_resumed_msg"></span></h6>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary"><a href="{{ route('advertisements') }}" id="advertisement_resumed_route">{{trans('lang.go')}}</a></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/bootstrap/js/popper.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('js/jquery.slimscroll.js') }}"></script>
        <script src="{{ asset('js/waves.js') }}"></script>
        <script src="{{ asset('js/sidebarmenu.js') }}"></script>
        <script src="{{ asset('assets/plugins/sticky-kit-master/dist/sticky-kit.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/sparkline/jquery.sparkline.min.js') }}"></script>
        <script src="{{ asset('js/custom.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/select2/dist/js/select2.min.js') }}"></script>
        <script src="{{ asset('js/toastr.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- Datatable script -->

        <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/js/bootstrap-timepicker.min.js"></script>
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
        <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
        <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.24/jspdf.plugin.autotable.min.js"></script>
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

        <script type="text/javascript">
            
            jQuery(window).scroll(function() {
                var scroll = jQuery(window).scrollTop();
                if (scroll <= 60) {
                    jQuery("body").removeClass("sticky");
                } else {
                    jQuery("body").addClass("sticky");
                }
            });           
            const datatableLang = {
                "decimal":        "",
                "emptyTable":     "{{ trans('lang.no_record_found') }}",
                "info":           "{{ trans('lang.datatable_info') }}", 
                "infoEmpty":      "{{ trans('lang.datatable_info_empty') }}", 
                "infoFiltered":   "{{ trans('lang.datatable_info_filtered') }}", 
                "lengthMenu":     "{{ trans('lang.datatable_length_menu') }}",
                "loadingRecords": "{{ trans('lang.loading') }}",
                "processing":     "{{ trans('lang.processing') }}",
                "search":         "{{ trans('lang.search') }}",
                "zeroRecords":    "{{ trans('lang.no_record_found') }}",
                "paginate": {
                    "first":      "{{ trans('lang.first') }}",
                    "last":       "{{ trans('lang.last') }}",
                    "next":       "{{ trans('lang.next') }}",
                    "previous":   "{{ trans('lang.previous') }}"
                },
                "aria": {
                    "sortAscending":  ": {{ trans('lang.sort_asc') }}",
                    "sortDescending": ": {{ trans('lang.sort_desc') }}"
                }
            };
        </script>

        <script src="{{ asset('js/chosen.jquery.js') }}"></script>
        <script src="{{ asset('js/bootstrap-tagsinput.js') }}"></script>

        {{-- Defined BEFORE @yield('scripts'): page scripts call these while they
             run, and anything declared after the yield does not exist yet. --}}
        <script type="text/javascript">

            /* The store the panel is currently working on.
             *
             * An owner is reached through `vendors.author`; an employee through
             * the single store named on their own user document. Until #3 lands
             * an owner has exactly one store, so the first result is the only
             * result - once an owner may have several, this is the one place
             * that has to learn which of them is selected.
             *
             * It replaced twenty page-local copies of the same lookup, several
             * of which threw when a store record was missing and one of which
             * returned nothing at all for employees.
             *
             * The firestore handle is taken per call rather than held in a
             * variable, so this block does not care whether firebase has been
             * initialised at the moment it is parsed. */
            async function resolveCurrentStore(ownerUserId) {
                const role = "{{ $authRole }}";
                const employeeStoreId = "{{ $empVendorId }}";

                try {
                    const db = firebase.firestore();

                    /* An employee belongs to one store and never chooses. */
                    if (role !== 'vendor') {
                        if (!employeeStoreId) {
                            return null;
                        }
                        const owned = await db.collection('vendors')
                            .where('id', '==', employeeStoreId).get();

                        return owned.empty ? null : owned.docs[0].data();
                    }

                    if (!ownerUserId) {
                        return null;
                    }

                    /* Every store on this account. Derived from `author` rather
                     * than stored, so it cannot drift. */
                    const stores = await db.collection('vendors')
                        .where('author', '==', ownerUserId).get();

                    if (stores.empty) {
                        return null;
                    }

                    /* users.vendorID names the store the owner has selected.
                     * It is honoured only when it still belongs to them - a
                     * store that was deleted, or an id left over from before,
                     * must not strand the panel on nothing. */
                    const userSnapshot = await db.collection('users').doc(ownerUserId).get();
                    const selectedId = userSnapshot.exists ? (userSnapshot.data().vendorID || '') : '';

                    if (selectedId) {
                        const match = stores.docs.find(function (doc) {
                            const store = doc.data();
                            return (store.id || doc.id) === selectedId;
                        });

                        if (match) {
                            return match.data();
                        }
                    }

                    /* No usable selection: fall back to the first store and
                     * record it, so the panel settles somewhere stable instead
                     * of picking again on every page. */
                    const first = stores.docs[0].data();
                    const firstId = first.id || stores.docs[0].id;

                    if (selectedId !== firstId) {
                        try {
                            await db.collection('users').doc(ownerUserId).update({ 'vendorID': firstId });
                        } catch (err) {
                            console.error("Could not record the selected store:", err);
                        }
                    }

                    return first;

                } catch (err) {
                    console.error("Error resolving the current store:", err);
                    return null;
                }
            }

            /* Switches the panel to another of this account's stores. Writes the
             * selection and reloads - every screen reads the selected store, so
             * the whole panel follows. */
            async function selectStore(ownerUserId, storeId) {
                const db = firebase.firestore();

                const stores = await db.collection('vendors')
                    .where('author', '==', ownerUserId).get();

                const owns = stores.docs.some(function (doc) {
                    return (doc.data().id || doc.id) === storeId;
                });

                if (!owns) {
                    return false;
                }

                await db.collection('users').doc(ownerUserId).update({ 'vendorID': storeId });

                /* The paywall flag is a MySQL column, written once at login. The
                 * store has just changed, so the answer may have changed with it
                 * - without this a vendor could switch from a subscribed store to
                 * an unsubscribed one and keep the access they had. */
                await refreshSubscriptionFlag(ownerUserId);

                return true;
            }

            /* Recomputes the paywall flag for the store now selected and records
             * it against the logged-in session. Failing to reach the server is
             * not fatal: the page still reloads and the flag is corrected at the
             * next login, so the vendor is never stranded by a network blip. */
            async function refreshSubscriptionFlag(ownerUserId) {
                try {
                    const subscribed = await storeIsSubscribed(ownerUserId);

                    await $.ajax({
                        type: 'POST',
                        url: "{{ route('setSubcriptionFlag') }}",
                        data: {
                            isSubscribed: subscribed ? 'true' : 'false'
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                } catch (err) {
                    console.error("Could not refresh the subscription flag:", err);
                }
            }

            /* The currency this panel should display prices in.
             *
             * A store belongs to a region, and a region names its own currency,
             * so a Cameroon store must read in FCFA even when the globally
             * active currency is something else. Falls back to the global
             * currency when the store has no region, or its region names no
             * currency.
             *
             * Shaped like a Firestore query on purpose - `.limit()`,
             * `.orderBy()` and a `.get()` yielding `{ docs: [ { data() } ] }` -
             * so a screen only swaps the query expression and keeps its own
             * logic untouched. Mirrors regionCurrencyRef() in the admin panel.
             */
            var storeCurrencyCache = {};

            async function getStoreCurrency() {
                const ownerUserId = "{{ $vendorUserId ?? '' }}";

                if (storeCurrencyCache.resolved !== undefined) {
                    return storeCurrencyCache.resolved;
                }

                const db = firebase.firestore();
                let currency = null;

                try {
                    const store = await resolveCurrentStore(ownerUserId);

                    if (store && store.regionId) {
                        const regionSnapshot = await db.collection('regions').doc(store.regionId).get();
                        const region = regionSnapshot.exists ? regionSnapshot.data() : null;

                        if (region && region.currencyId) {
                            const snapshot = await db.collection('currencies').doc(region.currencyId).get();
                            if (snapshot.exists) {
                                currency = snapshot.data();
                            }
                        }
                    }

                    if (currency == null) {
                        const fallback = await db.collection('currencies').where('isActive', '==', true).get();
                        if (fallback.docs.length > 0) {
                            currency = fallback.docs[0].data();
                        }
                    }
                } catch (err) {
                    console.error("Error resolving the store currency:", err);
                }

                storeCurrencyCache.resolved = currency;

                return currency;
            }

            function storeCurrencyRef() {
                return {
                    limit: function () { return this; },
                    orderBy: function () { return this; },
                    where: function () { return this; },
                    get: async function () {
                        const currency = await getStoreCurrency();

                        if (!currency) {
                            return { docs: [], empty: true, size: 0 };
                        }

                        return {
                            docs: [{
                                id: currency.id,
                                data: function () { return currency; }
                            }],
                            empty: false,
                            size: 1
                        };
                    }
                };
            }

            /* The id alone, for the many screens that only need it to filter by. */
            async function resolveCurrentStoreId(ownerUserId) {
                const store = await resolveCurrentStore(ownerUserId);

                return (store && store.id) ? store.id : '';
            }

            /* Fills the header's region list with the regions this account's
             * stores are in - not every region on the platform, which would
             * mostly be places the vendor does not trade in.
             *
             * An owner may hold stores in several regions; an employee belongs
             * to one store and sees that store's region alone.
             *
             * Choosing a region moves the panel to a store in it - see
             * switchToRegion() below. The panel works on one store at a time, so
             * "show me France" means "work on my France store".
             *
             * The header markup is included before this script block, so it
             * cannot call this itself - the ready handler below does, once
             * everything is parsed. */
            /* The account's stores, kept so the region picker can move between
             * them without asking Firestore again. */
            var headerRegionStores = [];

            async function loadHeaderRegions(ownerUserId) {
                const $select = $('#region_dropdown');

                if (!$select.length) {
                    return;
                }

                try {
                    const db = firebase.firestore();
                    const role = "{{ $authRole ?? '' }}";
                    const employeeStoreId = "{{ $empVendorId ?? '' }}";

                    let stores;

                    if (role === 'vendor') {
                        if (!ownerUserId) {
                            return;
                        }
                        stores = await db.collection('vendors').where('author', '==', ownerUserId).get();
                    } else {
                        if (!employeeStoreId) {
                            return;
                        }
                        stores = await db.collection('vendors').where('id', '==', employeeStoreId).get();
                    }

                    const regionIds = [];

                    headerRegionStores = [];

                    stores.docs.forEach(function (doc) {
                        const store = doc.data();
                        const regionId = store.regionId;

                        headerRegionStores.push({
                            id: store.id || doc.id,
                            regionId: regionId || '',
                            title: store.title || ''
                        });

                        if (regionId && regionIds.indexOf(regionId) === -1) {
                            regionIds.push(regionId);
                        }
                    });

                    if (!regionIds.length) {
                        return;
                    }

                    const regions = [];

                    for (const regionId of regionIds) {
                        const snapshot = await db.collection('regions').doc(regionId).get();

                        if (snapshot.exists) {
                            regions.push({ id: regionId, name: snapshot.data().name || '' });
                        }
                    }

                    if (!regions.length) {
                        return;
                    }

                    regions.sort(function (a, b) {
                        return a.name.localeCompare(b.name);
                    });

                    regions.forEach(function (region) {
                        $select.append($('<option></option>').attr('value', region.id).text(region.name));
                    });

                    /* Opens on the region the panel is working in. */
                    const current = await resolveCurrentStore(ownerUserId);

                    if (current && current.regionId) {
                        $select.val(current.regionId);
                    }

                    /* An employee belongs to one store and has nothing to move
                     * between, so the picker stays a label for them. */
                    if (role === 'vendor') {
                        $select.on('change', function () {
                            switchToRegion(ownerUserId, $(this).val());
                        });
                    }

                    $('#region_dropdown_box').css('display', 'flex');
                } catch (err) {
                    console.error("Could not load the header regions:", err);
                }
            }

            /* Moves the panel to a store in the chosen region.
             *
             * The panel works on one store at a time, so "show me France" means
             * "work on my France store". Everything - items, orders, point of
             * sale, coupons - follows the store, and so follows the region.
             *
             * With more than one store in that region the first by name is
             * taken; the vendor can pick a specific one from My Stores. If the
             * store already selected is in that region, nothing moves. */
            async function switchToRegion(ownerUserId, regionId) {
                if (!regionId) {
                    return;
                }

                const inRegion = headerRegionStores.filter(function (store) {
                    return store.regionId === regionId;
                });

                if (!inRegion.length) {
                    return;
                }

                const currentId = await resolveCurrentStoreId(ownerUserId);

                if (inRegion.some(function (store) { return store.id === currentId; })) {
                    return;
                }

                inRegion.sort(function (a, b) {
                    return a.title.localeCompare(b.title);
                });

                jQuery("#data-table_processing").show();

                const switched = await selectStore(ownerUserId, inRegion[0].id);

                if (!switched) {
                    jQuery("#data-table_processing").hide();
                    return;
                }

                window.location.reload();
            }

            $(document).ready(function () {
                loadHeaderRegions("{{ $vendorUserId ?? '' }}");
            });

            /* Whether the store the panel is working on has a subscription.
             *
             * A subscription belongs to the STORE, not the account - a vendor
             * with two stores subscribes each of them separately. The account
             * copy on `users` is still written, because the app reads it, but it
             * answers "has this vendor ever subscribed anything", which is not
             * the question a paywall should ask.
             *
             * Deliberately the same test as before - a plan id is present - and
             * not an expiry check. Adding expiry here would lock out anyone the
             * old gate let through, which is a separate decision. */
            async function storeIsSubscribed(ownerUserId) {
                const store = await resolveCurrentStore(ownerUserId);

                if (!store) {
                    return false;
                }

                const planId = store.subscriptionPlanId;

                return planId !== undefined && planId !== null && planId !== '';
            }

            /* ---- The vendor balance ----
             *
             * A store earns and spends its own money, so the balance lives on the
             * `vendors` document. The account copy on `users` is kept moving with
             * it because the customer app still credits and reads that one, and
             * the app is outside both repos.
             *
             * Both sides move by the same delta. The account total is deliberately
             * NOT recomputed as the sum of its stores: store balances start at
             * zero, so a sum would wipe out everything a vendor earned before
             * their stores had balances of their own.
             *
             * Amounts are written as numbers. Some existing records hold a string
             * here, because a couple of writers saved the result of .toFixed(),
             * which is why every read below is parsed before it is used. */
            function toAmount(value) {
                const amount = parseFloat(value);

                return isNaN(amount) ? 0 : amount;
            }

            async function storeWalletAmount(storeId) {
                if (!storeId) {
                    return 0;
                }

                const snapshot = await firebase.firestore().collection('vendors').doc(storeId).get();

                return snapshot.exists ? toAmount(snapshot.data().wallet_amount) : 0;
            }

            /* `delta` is signed: positive credits, negative debits. */
            async function applyVendorWalletDelta(storeId, ownerUserId, delta) {
                const db = firebase.firestore();
                const amount = toAmount(delta);

                if (amount === 0) {
                    return;
                }

                if (storeId) {
                    try {
                        const storeRef = db.collection('vendors').doc(storeId);
                        const snapshot = await storeRef.get();

                        if (snapshot.exists) {
                            /* The store names its owner, so a caller that knows
                             * only the store still moves both balances. */
                            if (!ownerUserId) {
                                ownerUserId = snapshot.data().author || '';
                            }

                            await storeRef.update({
                                'wallet_amount': Number((toAmount(snapshot.data().wallet_amount) + amount).toFixed(2))
                            });
                        }
                    } catch (err) {
                        console.error("Could not update the store balance:", err);
                    }
                }

                if (ownerUserId) {
                    try {
                        const userRef = db.collection('users').doc(ownerUserId);
                        const snapshot = await userRef.get();

                        if (snapshot.exists) {
                            await userRef.update({
                                'wallet_amount': Number((toAmount(snapshot.data().wallet_amount) + amount).toFixed(2))
                            });
                        }
                    } catch (err) {
                        console.error("Could not update the account balance:", err);
                    }
                }
            }

        </script>

        @yield('scripts')

        <script type="text/javascript">
        
            var route1 = '{{ route('orders.edit', ':id') }}';
            var booktable = '{{ route('booktable.edit', ':id') }}';

            var languages_list_main = [];
            var database = firebase.firestore();

            var version = database.collection('settings').doc("Version");

            version.get().then(async function(snapshots) {
                var version_data = snapshots.data();
                if (version_data == undefined) {
                    database.collection('settings').doc('Version').set({});
                }
                try {
                    $('.web_version').html("V:" + version_data.web_version);
                } catch (error) {

                }
            });

            var orderPlacedSubject = '';
            var orderPlacedMsg = '';
            var dineInPlacedSubject = '';
            var dineInPlacedMsg = '';
            var driverAcceptedMsg = '';
            var driverAcceptedSubject = '';
            var scheduleOrderPlacedSubject = '';
            var scheduleOrderPlacedMsg = '';
            var storeOrderCompletedSubject = '';
            var storeOrderCompletedMsg = '';
            var storeOrderAcceptedSubject = '';
            var storeOrderAcceptedMsg = '';

            var storeOrderInTransitSubject = "";
            var storeOrderInTransitMsg = "";
            var authRole = "{{ $authRole }}";
            var empVendorId = "{{ $empVendorId }}";
            
            database.collection('dynamic_notification').get().then(async function(snapshot) {
                if (snapshot.docs.length > 0) {
                    snapshot.docs.map(async (listval) => {
                        val = listval.data();

                        if (val.type == "dinein_placed") {

                            dineInPlacedSubject = val.subject;
                            dineInPlacedMsg = val.message;

                        } else if (val.type == "order_placed") {

                            orderPlacedSubject = val.subject;
                            orderPlacedMsg = val.message;

                        } else if (val.type == "driver_accepted") {

                            driverAcceptedSubject = val.subject;
                            driverAcceptedMsg = val.message;

                        } else if (val.type == "schedule_order") {

                            scheduleOrderPlacedSubject = val.subject;
                            scheduleOrderPlacedMsg = val.message;
                        } else if (val.type == "store_completed") {
                            storeOrderCompletedSubject = val.subject;
                            storeOrderCompletedMsg = val.message;

                        } else if (val.type == "store_accepted") {
                            storeOrderAcceptedSubject = val.subject;
                            storeOrderAcceptedMsg = val.message;

                        } else if (val.type == "store_intransit") {
                            storeOrderInTransitSubject = val.subject;
                            storeOrderInTransitMsg = val.message;

                        } else if (val.type == "advertisement_approved") {
                            advApprovedSub = val.subject;
                            advApprovedMsg = val.message;
                        } else if (val.type == "advertisement_cancelled") {
                            advCancelledSub = val.subject;
                            advCancelledMsg = val.message;
                        } else if (val.type == "advertisement_paused") {
                            advPausedSub = val.subject;
                            advPausedMsg = val.message;
                        } else if (val.type == "advertisement_resumed") {
                            advResumedSub = val.subject;
                            advResumedMsg = val.message;
                        }
                    });
                }
            });

            var pageloadded = 0;
            database.collection('vendor_orders').where('vendor.author', "==", cuser_id).onSnapshot(function(doc) {
                if (pageloadded) {
                    doc.docChanges().forEach(function(change) {
                        val = change.doc.data();
                        if (section_id == val.section_id) {
                            if (change.type == "added") {
                                if (val.status == "Order Placed") {

                                    if (val.scheduleTime != undefined && val.scheduleTime != null && val
                                        .scheduleTime != '') {
                                        $('.order_subject').text(scheduleOrderPlacedSubject);
                                        $('.order_message').text(scheduleOrderPlacedMsg);
                                    } else {
                                        $('.order_subject').text(orderPlacedSubject);
                                        $('.order_message').text(orderPlacedMsg);
                                    }
                                    if (route1) {
                                        jQuery("#notification_url").attr("href", route1.replace(':id', val.id));
                                    }
                                    jQuery("#notification_order").modal('show');

                                }
                            } else if (change.type == "modified") {

                                if (val.status == "Order Placed") {

                                    if (!val.hasOwnProperty('estimatedTimeToPrepare')) {

                                        if (route1) {
                                            jQuery("#notification_url").attr("href", route1.replace(':id', val
                                                .id));
                                        }
                                        if (val.scheduleTime != undefined && val.scheduleTime != null && val
                                            .scheduleTime != '') {
                                            $('.order_subject').text(scheduleOrderPlacedSubject);
                                            $('.order_message').text(scheduleOrderPlacedMsg);
                                        } else {
                                            $('.order_subject').text(orderPlacedSubject);
                                            $('.order_message').text(orderPlacedMsg);
                                        }
                                        jQuery("#notification_order").modal('show');
                                    }

                                } else if (val.status == "Driver Accepted") {
                                    $('.driver_accepted_subject').text(driverAcceptedSubject);
                                    $('.driver_accepted_msg').text(driverAcceptedMsg);
                                    if (route1) {
                                        jQuery("#notification_accepted_a").attr("href", route1.replace(':id',
                                            val.id));
                                    }
                                    jQuery("#notification_accepted_order").modal('show');
                                }
                            }
                        }
                    });
                } else {
                    pageloadded = 1;
                }
            });

            var pageloadded_book = 0;
            
            let vendorIdForDineIn = '';          

            var pageLoadedAdvertisement = 0;           
           
            database.collection('users').where('id', '==', cuser_id).get().then(async function(snapshots) {
                
                var userData = snapshots.docs[0].data();

                setCookie('isStoreDocumentVerify_'+userData.id, userData.isDocumentVerify, 365);
                setCookie('isAutoVerify_'+userData.id, userData.isAutoVerify, 365);
                setCookie('isvendorID_'+userData.id, userData.vendorID, 365);

                var photoURL = userData.profilePictureURL 
                    ? userData.profilePictureURL 
                    : "{{ asset('images/users/user-2.png') }}";

                document.querySelectorAll('.userimage').forEach(function(img) {
                    img.src = photoURL;
                    img.onerror = function() {
                        this.onerror = null;
                        this.src = "{{ asset('images/users/user-2.png') }}";
                    };
                });

                if (userData.hasOwnProperty('vendorID') && userData.vendorID != '' && userData.vendorID != null) {
                    vendorId = userData.vendorID;
                    database.collection('advertisements').where('vendorId', "==", vendorId).onSnapshot(function(doc) {
                        if (pageLoadedAdvertisement) {
                            doc.docChanges().forEach(function(change) {
                                val = change.doc.data();
                                var recentlyModifiedAd = localStorage.getItem('storeModifiedAd');

                                if (recentlyModifiedAd === val.id) {
                                    localStorage.removeItem('storeModifiedAd');
                                    return;
                                }
                                if (change.type == "modified") {
                                    var routeAdview = "{{ route('advertisements.view', ':id') }}";
                                    routeAdview = routeAdview.replace(':id', val.id);
                                    if (val.status == 'approved') {
                                        if (val.status == 'approved' && val.isPaused) {
                                            $('.advertisement_paused_sub').html(advPausedSub);
                                            $('.advertisement_paused_msg').html(advPausedMsg);
                                            $('#advertisement_paused_notification').modal('show');
                                            $('#advertisement_paused_route').attr('href', routeAdview);
                                        } else if (val.status == 'approved' && val.isPaused == null) {
                                            $('.advertisement_accepted_msg').html(advApprovedMsg);
                                            $('.advertisement_accepted_sub').html(advApprovedSub);
                                            $('#advertisement_accepted_notification').modal('show');
                                            $('#advertisement_accepted_route').attr('href', routeAdview);
                                        } else {
                                            $('.advertisement_resumed_sub').html(advResumedSub);
                                            $('.advertisement_resumed_msg').html(advResumedMsg);
                                            $('#advertisement_resumed_notification').modal('show');
                                            $('#advertisement_resumed_route').attr('href', routeAdview);
                                        }
                                    } else if (val.status == 'canceled') {
                                        $('.advertisement_cancelled_sub').html(advCancelledSub);
                                        $('.advertisement_cancelled_msg').html(advCancelledMsg);
                                        $('#advertisement_canceled_notification').modal('show');
                                        $('#advertisement_canceled_route').attr('href', routeAdview);
                                    }
                                }
                            })
                        } else {
                            pageLoadedAdvertisement = 1;
                        }
                    })
                }
                

                let vendorIdForOrders = '';

                if (authRole === 'vendor') {
                    vendorIdForOrders = cuser_id;           // Most common case
                    
                } 
                else if (authRole === 'employee') {
                    vendorIdForOrders = empVendorId;        // This is the vendor's ID for the employee
                }

                if (!vendorIdForOrders) {
                    console.log("No vendor ID found for notifications");
                    return;
                }

                let shouldListen = true;

                if (authRole === 'employee') {
                    const permResult = await getEmployeePermissionForTitle(cuser_id, 'Manage Order');
                    
                    if (permResult && permResult.isActive === true) {
                        shouldListen = true;                        
                    } else {
                        shouldListen = false;                       
                    }
                }

                if (shouldListen) {
                    var pageloadded = 0;

                    database.collection('vendor_orders')
                        .where('vendor.id', '==', vendorIdForOrders)     
                        .onSnapshot(function(doc) {

                            if (pageloadded) {
                                doc.docChanges().forEach(function(change) {
                                    val = change.doc.data();

                                    if (section_id == val.section_id) {

                                        if (change.type == "added") {
                                            if (val.status == "Order Placed") {

                                                if (val.scheduleTime != undefined && val.scheduleTime != null && val.scheduleTime != '') {
                                                    $('.order_subject').text(scheduleOrderPlacedSubject);
                                                    $('.order_message').text(scheduleOrderPlacedMsg);
                                                } else {
                                                    $('.order_subject').text(orderPlacedSubject);
                                                    $('.order_message').text(orderPlacedMsg);
                                                }
                                                if (route1) {
                                                    jQuery("#notification_url").attr("href", route1.replace(':id', val.id));
                                                }
                                                jQuery("#notification_order").modal('show');
                                            }
                                        } 
                                        else if (change.type == "modified") {

                                            if (val.status == "Order Placed" && !val.hasOwnProperty('estimatedTimeToPrepare')) {

                                                if (route1) {
                                                    jQuery("#notification_url").attr("href", route1.replace(':id', val.id));
                                                }
                                                if (val.scheduleTime != undefined && val.scheduleTime != null && val.scheduleTime != '') {
                                                    $('.order_subject').text(scheduleOrderPlacedSubject);
                                                    $('.order_message').text(scheduleOrderPlacedMsg);
                                                } else {
                                                    $('.order_subject').text(orderPlacedSubject);
                                                    $('.order_message').text(orderPlacedMsg);
                                                }
                                                jQuery("#notification_order").modal('show');

                                            } else if (val.status == "Driver Accepted") {
                                                $('.driver_accepted_subject').text(driverAcceptedSubject);
                                                $('.driver_accepted_msg').text(driverAcceptedMsg);
                                                if (route1) {
                                                    jQuery("#notification_accepted_a").attr("href", route1.replace(':id', val.id));
                                                }
                                                jQuery("#notification_accepted_order").modal('show');
                                            }
                                        }
                                    }
                                });
                            } else {
                                pageloadded = 1;
                            }
                        });
                }
                //Dine in request notification
                if (authRole === 'vendor') {
                    vendorIdForDineIn = cuser_id;          
                } 
                if (authRole === 'employee') {
                    let snapshot = await database.collection('vendors').doc(empVendorId).get();

                    if (snapshot.exists) {
                        let data = snapshot.data();
                        vendorIdForDineIn = data.author;  
                    } else {
                        console.log('Vendor not found');
                    }
                            
                }
                if (vendorIdForDineIn) {
                    let shouldListenDineIn = true;

                    if (authRole === 'employee') {
                        const permResult = await getEmployeePermissionForTitle(cuser_id, 'Dine in Request');
                        
                        if (permResult && permResult.isActive === true) {
                            shouldListenDineIn = true;
                        } else {
                            shouldListenDineIn = false;
                        }
                    }

                    if (shouldListenDineIn) {
                        var pageloadded_book = 0;

                        database.collection('booked_table')
                            .where('vendor.author', '==', vendorIdForDineIn)     
                            .onSnapshot(function(doc) {

                                if (pageloadded_book) {
                                    doc.docChanges().forEach(function(change) {
                                        val = change.doc.data();

                                        if (change.type == "added") {
                                            if (val.status == "Order Placed") {

                                                if (booktable) {
                                                    jQuery("#notification_book_table_url").attr("href", booktable.replace(':id', val.id));
                                                }
                                                $('.dinein_order_subject').text(dineInPlacedSubject);
                                                $('.dinein_order_msg').text(dineInPlacedMsg);
                                                jQuery("#notification_book_table_order").modal('show');
                                            }
                                        }
                                    });
                                } else {
                                    pageloadded_book = 1;
                                }
                            });
                    }
                }
            });

            var langcount = 0;
            var languages_list = database.collection('settings').doc('languages');
            languages_list.get().then(async function(snapshotslang) {
                snapshotslang = snapshotslang.data();
                if (snapshotslang != undefined) {
                    snapshotslang = snapshotslang.list;
                    languages_list_main = snapshotslang;
                    snapshotslang.forEach((data) => {
                        if (data.isActive == true) {
                            langcount++;
                            $('#language_dropdown').append($("<option></option>").attr("value", data.slug)
                                .text(data.title));
                        }
                    });
                    if (langcount > 1) {
                        $("#language_dropdown_box").css('visibility', 'visible');
                    }
                    <?php if (session()->get('locale')) { ?>
                    $("#language_dropdown").val("<?php echo session()->get('locale'); ?>");
                    <?php } ?>

                }
            });

            var url = "{{ route('changeLang') }}";

            $(".changeLang").change(function() {
                var slug = $(this).val();
                languages_list_main.forEach((data) => {
                    if (slug == data.slug) {
                        if (data.is_rtl == undefined) {
                            setCookie('is_rtl', 'false', 365);
                        } else {

                            setCookie('is_rtl', data.is_rtl.toString(), 365);
                        }
                        window.location.href = url + "?lang=" + slug;
                    }
                });
            });

            function getCookie(cname) {
                let name = cname + "=";
                let ca = document.cookie.split(';');
                for (let i = 0; i < ca.length; i++) {
                    let c = ca[i];
                    while (c.charAt(0) == ' ') {
                        c = c.substring(1);
                    }
                    if (c.indexOf(name) == 0) {
                        return c.substring(name.length, c.length);
                    }
                }
                return "";
            }
    
            function setCookie(cname, cvalue, exdays) {
                const d = new Date();
                d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
                let expires = "expires=" + d.toUTCString();
                document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
            }

            database.collection('settings').doc("notification_setting").get().then(async function(snapshots) {
                var data = snapshots.data();
                serviceJson = data.serviceJson;
                if (serviceJson != '' && serviceJson != null) {
                    $.ajax({
                        type: 'POST',
                        data: {
                            serviceJson: btoa(serviceJson),
                        },
                        url: "{{ route('storeServiceFile') }}",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(data) {
                            checkFlag = true;
                        }
                    });
                }
            });

            //On delete item delete image also from bucket general code
            const deleteDocumentWithImage = async (collection, id, singleImageField, arrayImageField) => {
                // Reference to the Firestore document
                const docRef = database.collection(collection).doc(id);
                try {
                    const doc = await docRef.get();
                    if (!doc.exists) {
                        console.log("No document found for deletion");
                        return;
                    }

                    const data = doc.data();

                    // Handle single image deletion
                    const item_file_name = data[singleImageField] || ''; // Single image field
                    if (item_file_name) {
                        await deleteImageFromBucket(item_file_name);
                    }

                    // Handle array of images deletion
                    const item_photos = data[arrayImageField] || []; // Photos array field
                    if (item_photos.length > 0) {
                        for (let i = 0; i < item_photos.length; i++) {
                            const photoUrl = item_photos[i];
                            if (photoUrl) {
                                await deleteImageFromBucket(photoUrl);
                            }
                        }
                    }

                    // Handle variant images deletion            
                    const item_attribute = data.item_attribute || {}; // Access item_attribute
                    const variants = item_attribute.variants || []; // Access variants array inside item_attribute
                    if (variants.length > 0) {
                        for (let i = 0; i < variants.length; i++) {
                            const variantImageUrl = variants[i].variant_image;
                            if (variantImageUrl) {
                                await deleteImageFromBucket(variantImageUrl);
                            }
                        }
                    }

                    // Optionally delete the Firestore document after image deletion
                    await docRef.delete();
                } catch (error) {
                    console.error("Error deleting document and images:", error);
                }
            };

            const deleteImageFromBucket = async (imageUrl) => {
                try {
                    const storageRef = firebase.storage().ref();

                    // Check if the imageUrl is a full URL or just a child path
                    let oldImageUrlRef;
                    if (imageUrl.includes('https://')) {
                        // Full URL
                        oldImageUrlRef = storageRef.storage.refFromURL(imageUrl);
                    } else {
                        // Child path, use ref instead of refFromURL
                        oldImageUrlRef = storageRef.storage.ref(imageUrl);
                    }
                    var envBucket = "<?php echo env('FIREBASE_STORAGE_BUCKET'); ?>";
                    var imageBucket = oldImageUrlRef.bucket;
                    // Check if the bucket name matches
                    if (imageBucket === envBucket) {
                        // Delete the image
                        await oldImageUrlRef.delete();
                        console.log("Image deleted successfully.");
                    }
                } catch (error) {

                }
            };

            function exportData(dt, format, config) {
                const {
                    columns,
                    fileName = 'Export',
                } = config;

                const filteredRecords = dt.ajax.json().filteredData;

                const fieldTypes = {};
                const dataMapper = (record) => {
                    return columns.map((col) => {
                        const value = record[col.key];
                        if (!fieldTypes[col.key]) {
                            if (value === true || value === false) {
                                fieldTypes[col.key] = 'boolean';
                            } else if (value && typeof value === 'object' && value.seconds) {
                                fieldTypes[col.key] = 'date';
                            } else if (typeof value === 'number') {
                                fieldTypes[col.key] = 'number';
                            } else if (typeof value === 'string') {
                                fieldTypes[col.key] = 'string';
                            } else {
                                fieldTypes[col.key] = 'string';
                            }
                        }

                        switch (fieldTypes[col.key]) {
                            case 'boolean':
                                return value ? 'Yes' : 'No';
                            case 'date':
                                return value ? new Date(value.seconds * 1000).toLocaleString() : '-';
                            case 'number':
                                return typeof value === 'number' ? value : 0;
                            case 'string':
                            default:
                                return value || '-';
                        }
                    });
                };

                const tableData = filteredRecords.map(dataMapper);

                const data = [columns.map(col => col.header), ...tableData];

                const columnWidths = columns.map((_, colIndex) =>
                    Math.max(...data.map(row => row[colIndex]?.toString().length || 0))
                );

                if (format === 'csv') {
                    const csv = data.map(row => row.map(cell => {
                        if (typeof cell === 'string' && (cell.includes(',') || cell.includes('\n') || cell.includes(
                                '"'))) {
                            return `"${cell.replace(/"/g, '""')}"`;
                        }
                        return cell;
                    }).join(',')).join('\n');

                    const blob = new Blob([csv], {
                        type: 'text/csv;charset=utf-8;'
                    });
                    saveAs(blob, `${fileName}.csv`);
                } else if (format === 'excel') {
                    const ws = XLSX.utils.aoa_to_sheet(data, {
                        cellDates: true
                    });

                    ws['!cols'] = columnWidths.map(width => ({
                        wch: Math.min(width + 5, 30)
                    }));

                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, 'Data');
                    XLSX.writeFile(wb, `${fileName}.xlsx`);
                } else if (format === 'pdf') {

                    const {
                        jsPDF
                    } = window.jspdf;
                    const doc = new jsPDF('l', 'mm', 'a4'); // Landscape for more width

                    doc.setFontSize(12);
                    doc.text(fileName, 14, 16);

                    doc.autoTable({
                        head: [columns.map(col => col.header)],
                        body: tableData,
                        startY: 20,
                        theme: 'striped',
                        styles: {
                            cellPadding: 1,
                            fontSize: 8,
                            overflow: 'linebreak',
                        },
                        columnStyles: {
                            0: {
                                cellWidth: 'auto'
                            }, // Adjust first column automatically
                        },
                        margin: {
                            top: 30,
                            bottom: 30
                        },
                        pageBreak: 'auto', // Ensures page break for long content
                    });

                    doc.save(`${fileName}.pdf`);

                } else {
                    console.error('Unsupported format');
                }
            }
            async function getEmployeePermissionForTitle(userId, permissionTitle) {
                if (!userId || !permissionTitle) {
                    return { isActive: false };
                }

                try {
                    // 1. Get user
                    const userSnap = await database.collection('users')
                        .where('id', '==', userId)
                        .limit(1)
                        .get();

                    if (userSnap.empty) {
                        return { isActive: false };
                    }

                    const userData = userSnap.docs[0].data();

                    if (!userData.employeePermissionId) {
                        return { isActive: false };
                    }

                    // 2. Get role
                    const roleSnap = await database.collection('vendor_employee_roles')
                        .doc(userData.employeePermissionId)
                        .get();

                    if (!roleSnap.exists) {
                        return { isActive: false };
                    }

                    const roleData = roleSnap.data();

                    if (roleData.isEnable !== true) {
                        return { isActive: false };
                    }

                    // 3. Find permission
                    const perm = (roleData.permissions || []).find(p => p.title === permissionTitle);

                    if (!perm) {
                        return { isActive: false };
                    }

                    // Return the same field name you're already using
                    return {
                        isActive: !!perm.isActive
                    };

                } catch (err) {
                    console.error("Error fetching employee permission:", err);
                    return { isActive: false };
                }
            }
            async function getCountryFromLatLng(lat, lng) {
                const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`;
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                return data?.address?.country || '';
            }

            /* A store's region: its own regionId, else the region its zone belongs
             * to. Anything a store creates inherits this, otherwise the record is
             * invisible once an admin filters by region.
             *
             * The zone fallback covers stores stamped before regions existed that
             * have not saved their profile since. */
            async function resolveVendorRegionId(vendor) {
                if (!vendor) {
                    return '';
                }

                if (vendor.regionId) {
                    return vendor.regionId;
                }

                if (vendor.zoneId) {
                    try {
                        const zoneSnap = await database.collection('zone').doc(vendor.zoneId).get();
                        if (zoneSnap.exists) {
                            const zoneData = zoneSnap.data();
                            return zoneData.regionId ? zoneData.regionId : '';
                        }
                    } catch (err) {
                        console.error("Error resolving store region:", err);
                    }
                }

                return '';
            }

            function formatCurrency(amount, currency = {}) {
                const symbol = currency.symbol || '';
                const decimals = currency.decimal_degits ?? 2;
                const symbolAtRight = Boolean(currency.symbolAtRight);
                const formatted = parseFloat(amount).toFixed(decimals);
                return symbolAtRight
                    ? formatted + ' ' + symbol
                    : symbol + formatted;
            }

        </script>
    </body>

</html>
