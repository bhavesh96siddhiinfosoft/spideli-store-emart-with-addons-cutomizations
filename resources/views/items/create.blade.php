@extends('layouts.app')

@section('content')
    {{-- The layout's @yield('style') is commented out, so the editor's stylesheet
         is linked here. Only these two screens use it, so it is not worth putting
         in the layout for every page to load. --}}
    <link href="{{ asset('assets/plugins/summernote/summernote-bs4.css') }}" rel="stylesheet">
    <div class="page-wrapper">
        <div class="row page-titles">

            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.item_plural') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{!! route('dashboard') !!}">{{ trans('lang.dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item"><a href="{!! route('items') !!}">{{ trans('lang.item_plural') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.item_create') }}</li>
                </ol>
            </div>
        </div>

        <div>
            <div class="card-body">
                <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">
                    {{ trans('lang.processing') }}
                </div>
                <div class="error_top" style="display:none"></div>
                <div class="row vendor_payout_create">
                    <div class="vendor_payout_create-inner">

                        <fieldset>

                            <legend>{{ trans('lang.item_information') }}</legend>

                            <div class="form-group row width-100" id="admin_commision_info" style="display:none">
                                <div class="m-3">
                                    <div class="form-text font-weight-bold text-danger h6">
                                        {{ trans('lang.price_instruction') }}</div>
                                    <div class="form-text font-weight-bold text-danger h6" id="admin_commision"></div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                 @if (isset($openai_settings) && data_get($openai_settings, 'status') == true)
                                    <div class="col-12"> 
                                        <label class="control-label">{{ trans('lang.item_name') }}</label>
                                        <button type="button" class="btn bg-white text-primary generate_btn_wrapper opacity-1 pl-1 mb-2 auto_fill_title"
                                            data-error="{{ trans('lang.ai_name_error') }}"
                                            data-lang="{{ App::getLocale() }}"
                                            data-route="{{ route('ai.title-auto-fill') }}">
                                            <div class="btn-svg-wrapper">
                                                <img width="18" height="18" class="" src="{{ asset('images/svg/blink-icon-orange.svg') }}" alt="">
                                            </div>
                                            <span class="ai-text-animation d-none" role="status">
                                                {{ trans('lang.ai_just_asecond') }}
                                            </span>
                                            <span class="btn-text">{{ trans('lang.ai_generate') }}</span>
                                        </button>
                                        <div class="col-7 outline-wrapper">
                                            <input type="text" class="form-control item_name" id="item_name" required>
                                        </div>
                                    </div>
                                @else
                                    <label class="control-label col-3">{{ trans('lang.item_name') }}</label>
                                    <div class="col-7">
                                        <input type="text" class="form-control item_name" id="item_name" required>
                                    </div>
                                @endif
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.item_price') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control item_price" required>
                                    <div class="form-text text-muted">
                                        {{ trans('lang.item_price_help') }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.item_discount') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control item_discount">
                                    <div class="form-text text-muted">
                                        {{ trans('lang.item_discount_help') }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-check width-100">
                                <input type="checkbox" class="wholesale_enabled" id="wholesale_enabled">
                                <label class="col-3 control-label"
                                    for="wholesale_enabled">{{ trans('lang.offer_at_wholesale') }}</label>
                                <div class="form-text text-muted">
                                    {{ trans('lang.offer_at_wholesale_help') }}
                                </div>
                            </div>

                            <div class="wholesale_fields" style="display:none;">
                                {{-- Retail / wholesale / both, as the store app writes it. With wholesale
                                     switched off the product is retail whatever is chosen here, so the
                                     value is only read from this dropdown while wholesale is on. --}}
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.sale_type') }}</label>
                                    <div class="col-7">
                                        <div class="custom-dropdown">
                                            <select class="form-control sale_type" id="sale_type">
                                                <option value="retail">{{ trans('lang.sale_type_retail') }}</option>
                                                <option value="both" selected>{{ trans('lang.sale_type_both') }}</option>
                                                <option value="wholesale">{{ trans('lang.sale_type_wholesale') }}</option>
                                            </select>
                                        </div>
                                        <div class="form-text text-muted">{{ trans('lang.sale_type_help') }}</div>
                                    </div>
                                </div>

                                {{-- One list, tier one included. The store app shows the same thing, and
                                     its `wholesaleTiers` array is the shape both sides write. The legacy
                                     `wholesalePrice` + `wholesaleMinQty` pair is still saved, taken from
                                     tier one, because the website prices from it. --}}
                                <div class="form-group row width-100">
                                    <div class="col-12">
                                        <label class="control-label">{{ trans('lang.wholesale_tiers') }}</label>
                                        <div id="wholesale_tiers"></div>
                                        <button type="button" class="btn btn-primary mt-2" id="add_wholesale_tier">{{ trans('lang.add_tier') }}</button>
                                        <div class="form-text text-muted">{{ trans('lang.wholesale_tiers_help') }}</div>
                                    </div>
                                </div>

                                {{-- Same shape as the Description field below, so the two boxes
                                     line up at the same width. --}}
                                <div class="form-group row width-100">
                                    <div class="col-12">
                                        <label class="control-label">{{ trans('lang.wholesale_details') }}</label>
                                        <div class="col-7 outline-wrapper">
                                            <textarea id="wholesale_details"></textarea>
                                            <div class="form-text text-muted">
                                                {{ trans('lang.wholesale_details_help') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.item_category_id') }}</label>
                                <div class="d-flex page-content">
                                    <div class="col-7">
                                        <select id='item_category' class="form-control" required>
                                            <option value="">{{ trans('lang.select_category') }}</option>
                                        </select>
                                        <div class="form-text text-muted">
                                            {{ trans('lang.item_category_id_help') }}
                                        </div>
                                    </div>
                                    @if (isset($openai_settings) && data_get($openai_settings, 'status') == true)
                                    <div class="width-100 text-right">
                                        <button type="button" class="btn bg-white text-primary generate_btn_wrapper opacity-1 pl-1 mb-2 variation_setup_auto_fill"
                                            data-error="{{ trans('lang.ai_name_description_error') }}"
                                            data-lang="{{ App::getLocale() }}"
                                            data-route="{{ route('ai.variation-setup-auto-fill') }}">
                                            <div class="btn-svg-wrapper">
                                                <img width="18" height="18" class="" src="{{ asset('images/svg/blink-icon-orange.svg') }}" alt="">
                                            </div>
                                            <span class="ai-text-animation d-none" role="status">
                                                {{ trans('lang.ai_just_asecond') }}
                                            </span>
                                            <span class="btn-text">{{ trans('lang.ai_generate') }}</span>
                                        </button>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div class="form-check width-50" id="is_digital_div" style="display: none;">
                                <input type="checkbox" class="is_digital_product" id="is_digital_product">
                                <label class="col-3 control-label"
                                    for="item_publish">{{ trans('lang.item_is_digital') }}</label>
                            </div>

                            <div class="form-group width-100" id="upload_file_div" style="display: none;">
                                <label class="col-3 control-label">{{ trans('lang.item_upload_file') }}</label>
                                <div class="col-7">
                                    <input type="file" onChange="handleZipUpload(event)" id="digital_product_file">
                                    <div id="uploding_zip" class="placeholder_img_thumb"></div>
                                    <div class="form-text text-muted max_file_size"></div>
                                    <div class="form-text text-muted">{{ trans('lang.item_upload_file_ext') }}</div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.item_quantity') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control item_quantity" value="-1">
                                    <div class="form-text text-muted">
                                        {{ trans('lang.item_quantity_help') }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-50 brandDiv" style="display: none;">
                                <label class="col-3 control-label">{{ trans('lang.brand') }}</label>
                                <div class="col-7">
                                    <select id='brand' class="form-control" required>
                                        <option value="">{{ trans('lang.select_brand') }}</option>
                                    </select>
                                    <div class="form-text text-muted">
                                        {{ trans('lang.brand_help') }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-100" id="attributes_div" style="display:none">
                                <label class="col-3 control-label">{{ trans('lang.item_attribute_id') }}</label>
                                <div class="col-7">
                                    <select id='item_attribute' class="form-control chosen-select" required
                                        multiple="multiple" style="display: none;" onchange="selectAttribute();"></select>
                                </div>
                            </div>

                            <div class="form-group row width-100" id="attributes_div_values">
                                <div class="item_attributes" id="item_attributes"></div>
                                <div class="item_variants" id="item_variants"></div>
                                <input type="hidden" id="attributes" value="" />
                                <input type="hidden" id="variants" value="" />
                            </div>

                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.item_image') }}</label>
                                <div class="col-7">
                                    <input type="file" onChange="handleFileSelectProduct(event)">
                                    <div class="placeholder_img_thumb product_image"></div>
                                    <div id="uploding_image"></div>
                                    <div class="form-text text-muted">
                                        {{ trans('lang.item_image_help') }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-100 desciption-wrapper">
                                @if (isset($openai_settings) && data_get($openai_settings, 'status') == true)
                                    <div class="col-12"> 
                                        <label class="control-label">{{ trans('lang.item_description') }}</label>
                                        <button type="button" class="btn bg-white text-primary generate_btn_wrapper opacity-1 pl-1 mb-2 auto_fill_description"
                                            data-error="{{ trans('lang.ai_description_error') }}"
                                            data-lang="{{ App::getLocale() }}"
                                            data-route="{{ route('ai.description-auto-fill') }}">
                                            <div class="btn-svg-wrapper">
                                                <img width="18" height="18" class="" src="{{ asset('images/svg/blink-icon-orange.svg') }}" alt="">
                                            </div>
                                            <span class="ai-text-animation d-none" role="status">
                                                {{ trans('lang.ai_just_asecond') }}
                                            </span>
                                            <span class="btn-text">{{ trans('lang.ai_generate') }}</span>
                                        </button>
                                        <div class="col-7 outline-wrapper">
                                            <textarea rows="8" class="form-control item_description" id="item_description"></textarea>
                                        </div>
                                    </div>
                                @else
                                    <label class="control-label col-3">{{ trans('lang.item_description') }}</label>
                                    <div class="col-7">
                                        <textarea rows="8" class="form-control item_description" id="item_description"></textarea>
                                    </div>    
                                @endif
                            </div>
                            <div class="form-check width-100">
                                <input type="checkbox" class="item_publish" id="item_publish">
                                <label class="col-3 control-label"
                                    for="item_publish">{{ trans('lang.item_publish') }}</label>
                            </div>

                            <div class="form-check width-100 food_delivery_div d-none">
                                <input type="checkbox" class="item_nonveg" id="item_nonveg">
                                <label class="col-3 control-label" for="item_nonveg">{{ trans('lang.non_veg') }}</label>
                            </div>

                            <div class="form-check width-100 food_delivery_take_away d-none">
                                <input type="checkbox" class="item_take_away_option" id="item_take_away_option">
                                <label class="col-3 control-label"
                                    for="item_take_away_option">{{ trans('lang.item_take_away') }}</label>
                                <div class="form-text text-muted">{{ trans('lang.item_take_away_help') }}</div>
                            </div>

                        </fieldset>
                        <fieldset class="product-taxes d-none">
                            <legend>{{ trans('lang.tax_settings') }}</legend>
                            <div class="form-group row">
                                <label class="col-3 control-label">{{ trans('lang.select_taxes') }}</label>
                                <div class="col-7">
                                    <select id="taxes" class="form-control chosen-select" multiple="multiple"></select>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="food_delivery_div ingredients-wrapper d-none">

                            <legend>{{ trans('lang.ingredients') }}</legend>
                            @if (isset($openai_settings) && data_get($openai_settings, 'status') == true)
                            <div class="width-100 text-right">
                                <button type="button" class="btn bg-white text-primary generate_btn_wrapper opacity-1 pl-1 mb-2 ingredients_auto_fill"
                                    data-error="{{ trans('lang.ai_ingredients_error') }}"
                                    data-lang="{{ App::getLocale() }}"
                                    data-route="{{ route('ai.ingredients-auto-fill') }}">
                                    <div class="btn-svg-wrapper">
                                        <img width="18" height="18" class="" src="{{ asset('images/svg/blink-icon-orange.svg') }}" alt="">
                                    </div>
                                    <span class="ai-text-animation d-none" role="status">
                                        {{ trans('lang.ai_just_asecond') }}
                                    </span>
                                    <span class="btn-text">{{ trans('lang.ai_generate') }}</span>
                                </button>
                            </div>
                            @endif
                            <div class="outline-wrapper">
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.calories') }}</label>
                                    <div class="col-7">
                                        <input type="number" class="form-control item_calories">
                                    </div>
                                </div>

                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.grams') }}</label>
                                    <div class="col-7">
                                        <input type="number" class="form-control item_grams">
                                    </div>
                                </div>

                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.fats') }}</label>
                                    <div class="col-7">
                                        <input type="number" class="form-control item_fats">
                                    </div>
                                </div>

                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.proteins') }}</label>
                                    <div class="col-7">
                                        <input type="number" class="form-control item_proteins">
                                    </div>
                                </div>
                            </div>

                        </fieldset>

                        <fieldset class="addons-wrapper">
                            <legend>{{ trans('lang.item_add_one') }}</legend>
                            @if (isset($openai_settings) && data_get($openai_settings, 'status') == true)
                            <div class="width-100 text-right">
                                <button type="button" class="btn bg-white text-primary generate_btn_wrapper opacity-1 pl-1 mb-2 addons_auto_fill"
                                    data-error="{{ trans('lang.ai_addons_error') }}"
                                    data-lang="{{ App::getLocale() }}"
                                    data-route="{{ route('ai.addons-auto-fill') }}">
                                    <div class="btn-svg-wrapper">
                                        <img width="18" height="18" class="" src="{{ asset('images/svg/blink-icon-orange.svg') }}" alt="">
                                    </div>
                                    <span class="ai-text-animation d-none" role="status">
                                        {{ trans('lang.ai_just_asecond') }}
                                    </span>
                                    <span class="btn-text">{{ trans('lang.ai_generate') }}</span>
                                </button>
                            </div>
                            @endif
                             <div class="outline-wrapper">
                                <div class="form-group add_ons_list extra-row">
                                </div>

                                <div class="form-group row width-100">
                                    <div class="col-7">
                                        <button type="button" onclick="addOneFunction()" class="btn btn-primary"
                                            id="add_one_btn">{{ trans('lang.item_add_one') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group row width-100" id="add_ones_div" style="display:none">
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="col-3 control-label">{{ trans('lang.item_title') }}</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control add_ons_title">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label class="col-3 control-label">{{ trans('lang.item_price') }}</label>
                                            <div class="col-7">
                                                <input type="number" class="form-control add_ons_price">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row save_add_one_btn width-100" style="display:none">
                                    <div class="col-7">
                                        <button type="button" onclick="saveAddOneFunction()" class="btn btn-primary">
                                            {{ trans('lang.save_add_ones') }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </fieldset>
                        <fieldset class="specification-wrapper">

                            <legend>{{ trans('lang.product_specification') }}</legend>
                            @if (isset($openai_settings) && data_get($openai_settings, 'status') == true)
                            <div class="width-100 text-right">
                                <button type="button" class="btn bg-white text-primary generate_btn_wrapper opacity-1 pl-1 mb-2 specification_auto_fill"
                                    data-error="{{ trans('lang.ai_specification_error') }}"
                                    data-lang="{{ App::getLocale() }}"
                                    data-route="{{ route('ai.specification-auto-fill') }}">
                                    <div class="btn-svg-wrapper">
                                        <img width="18" height="18" class="" src="{{ asset('images/svg/blink-icon-orange.svg') }}" alt="">
                                    </div>
                                    <span class="ai-text-animation d-none" role="status">
                                        {{ trans('lang.ai_just_asecond') }}
                                    </span>
                                    <span class="btn-text">{{ trans('lang.ai_generate') }}</span>
                                </button>
                            </div>
                            @endif
                            <div class="outline-wrapper">
                                <div class="form-group product_specification extra-row">
                                </div>

                                <div class="form-group row width-100">
                                    <div class="col-7">
                                        <button type="button" onclick="addProductSpecificationFunction()"
                                            class="btn btn-primary" id="add_one_btn">
                                            {{ trans('lang.add_product_specification') }}
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group row width-100" id="add_product_specification_div"
                                    style="display:none">
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="col-3 control-label">{{ trans('lang.lable') }}</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control add_label">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label class="col-3 control-label">{{ trans('lang.value') }}</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control add_value">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row save_product_specification_btn width-100" style="display:none">
                                    <div class="col-7">
                                        <button type="button" onclick="saveProductSpecificationFunction()"
                                            class="btn btn-primary">{{ trans('lang.save_product_specification') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>

                <div class="form-group col-12 text-center btm-btn">
                    <button type="button" class="btn btn-primary  create_item_btn"><i class="fa fa-save"></i>
                        {{ trans('lang.save') }}
                    </button>
                    <a href="{!! route('items') !!}" class="btn btn-default"><i
                            class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                </div>
            </div>
        </div>
    </div>
    @includeif('layouts.ai_sidebar')
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/summernote/summernote-bs4.js') }}"></script>
    @if (isset($openai_settings) && data_get($openai_settings, 'status') == true)
        <link href="{{ asset('css/AI/ai-sidebar.css') }}" rel="stylesheet">
        <script src="{{ asset('js/AI/product-details-autofill.js') }}"></script>
        <script src="{{ asset('js/AI/variation-setup-auto-fill.js') }}"></script>
        <script src="{{ asset('js/AI/ai-sidebar.js') }}"></script>
        <script src="{{ asset('js/AI/compressor/image-compressor.js')}}"></script>
        <script src="{{ asset('js/AI/compressor/compressor.min.js')}}"></script>
    @endif
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/crypto-js.js"></script>


    <script>
        var database = firebase.firestore();
        var addOnesTitle = [];
        var addOnesPrice = [];
        var vendor_categories = "";
        var brand = "";
        var attributes_list = [];
        var brand_list = [];
        var photos = [];
        var product_image_filename = [];
        var variant_photos = [];
        var variant_filename = [];
        var variant_vIds = [];
        var productImagesCount = 0;
        var itemLimit = '-1';
        var createdItem = 0;
        var photo = "";
        var product_specification = {};
        var vandorId;
        var section_id;
        var vendorUserId = "<?php echo $id; ?>";
        var digital_product_file = '';
        var digital_product_file_name = '';
        var digital_product_ext = '';
        var section_flag = '';
        var allowed_file_size = '';
        var subscriptionModel = false;
        var commissionModel = false;
        var currentCurrency = '';
        var currencyAtRight = false;
        var decimal_degits = 0;
        var categories_list=[];
        var authRole = "{{ $authRole }}";
        var empVendorId = "{{ $empVendorId }}";
        let currentPermissions = {
            isActive: true   
        };
        var vendorLatitude = '';
        var vendorLongitude = '';
        var countryName = '';
        var subscriptionBusinessModel = database.collection('settings').doc("vendor");
        subscriptionBusinessModel.get().then(async function(snapshots) {
                var subscriptionSetting = snapshots.data();
                if (subscriptionSetting.subscription_model == true) {
                    subscriptionModel = true;
                }
            });
        var refCurrency = storeCurrencyRef();
        refCurrency.get().then(async function(snapshots) {
            var currencyData = snapshots.docs[0].data();
            currentCurrency = currencyData.symbol;
            currencyAtRight = currencyData.symbolAtRight;

            if (currencyData.decimal_degits) {
                decimal_degits = currencyData.decimal_degits;
            }
        });
        
        database.collection('users').where('id', '==', vendorUserId).get().then(async function(snapshots) {
            var data = snapshots.docs[0].data();
            if (subscriptionModel) {
                if (data.hasOwnProperty('subscription_plan') && data.subscription_plan != null && data
                    .subscription_plan != '') {
                    itemLimit = data.subscription_plan.itemLimit;
                }
            }

        });
        $(document).ready(async function() {   
            /* Built here, not only from variants_update() - that runs when the
             * variant table is rebuilt, which on a new product never happens, so
             * the editor was never created and the plain textarea showed. */
            initWholesaleEditor();

            if (authRole === 'employee') {               
                const perm = await getEmployeePermissionForTitle(vendorUserId, "Manage Products");
                currentPermissions = {
                    isActive: perm.isActive ?? false
                };
                if (!currentPermissions.isActive) {
                    alert('{{ trans("lang.no_permission") }}');
                    $('.page-btn').hide();
                    $('.restaurant_payout_create').html('<p class="text-center text-danger font-weight-bold">{{ trans("lang.no_permission") }}</p>');
                    return;
                }
            }             
        });
        /* The store DOCUMENT, not just its id. This screen also needs the store's
         * section, its commission settings and its coordinates, so it cannot use
         * resolveCurrentStoreId() - that returns a plain string, and reading
         * `.id` off a string gave undefined, which made the vendor_products
         * query below throw "Unsupported field value: undefined" and left the
         * form half built (no categories, no taxes, no section flags). */
        resolveCurrentStore(vendorUserId).then(async data => {

            /* No store selected yet - nothing on this form can be filled in. */
            if (!data) {
                return;
            }

            vandorId = data.id;
            vendorLatitude = data.latitude;
            vendorLongitude = data.longitude;
            countryName = getCookie('vendorCountryName');

            itemCountRef = await database.collection('vendor_products').where('vendorID', '==', vandorId).get();
            createdItem = itemCountRef.size;
            section_id = data.section_id;
           
            database.collection('settings').doc('globalSettings').get().then(async function(snapshots) {
               
                let globalTax = snapshots.data();
               
                if (!countryName && (vendorLatitude && vendorLongitude)) {
                    countryName = await getCountryFromLatLng(vendorLatitude,vendorLongitude);
                    setCookie('countryName', countryName, 365);
                }
                if(globalTax.taxScope == "product" && countryName){
                   
                    $(".product-taxes").removeClass('d-none');
                    $('#taxes').chosen('destroy').empty();
                    database.collection('tax').where('enable','==',true).where('scope','==','product').where('country','==',countryName).where('sectionId','==',section_id).get().then(async function(snapshots) {
                        if(snapshots.docs.length > 0){
                            snapshots.docs.forEach((listval) => {
                                var data = listval.data();
                                let taxText = data.title + ' (';
                                if (data.type === 'percentage') {
                                    taxText += data.tax + '%';
                                } else {
                                    if (currencyAtRight) {
                                        taxText += parseFloat(data.tax).toFixed(decimal_degits) + ' ' + currentCurrency;
                                    } else {
                                        taxText += currentCurrency + parseFloat(data.tax).toFixed(decimal_degits);
                                    }
                                }
                                taxText += ')';
                                $('#taxes').append(
                                    $('<option></option>')
                                        .attr('value', data.id)
                                        .attr('data-tax', encodeURIComponent(JSON.stringify(data)))
                                        .text(taxText)
                                );
                            })
                            $('#taxes').chosen({
                                width: '100%',
                                placeholder_text_multiple: '{{ trans('lang.select_taxes') }}',
                            });
                        }else{
                            $(".product-taxes").addClass('d-none');        
                        }
                    });
                }else{
                    $(".product-taxes").addClass('d-none');
                }
            });

            if (section_id) {
                var section = database.collection('sections').where('id', '==', section_id);
                await section.get().then(async function(snapshots) {
                    var section_data = snapshots.docs[0].data();
                    section_flag = section_data.serviceTypeFlag;

                    if (section_data.serviceTypeFlag == "ecommerce-service") {
                        $('.brandDiv').show();
                        $("#is_digital_div").show();
                    }

                    if (section_data.serviceTypeFlag == "delivery-service") {
                        $('.food_delivery_take_away').removeClass('d-none');

                        if (section_data.is_product_details) {
                            $('.food_delivery_div').removeClass('d-none');
                        }
                    }
                    if (section_data.adminCommision != null && section_data
                        .adminCommision != '') {
                        if (section_data.adminCommision.enable) {
                            commissionModel = true;
                            
                        }
                    }
                });
            } else {
                $('.brandDiv').hide();
                $("#brand").val('');
                $("#is_digital_div").hide();
            }

            if (data.section_id != undefined && data.section_id != '') {
                vendor_categories = database.collection('vendor_categories').where('section_id',
                    '==', data.section_id);
                brand = database.collection('brands').where('sectionId', '==', data.section_id);

            } else {
                vendor_categories = database.collection('vendor_categories');
                brand = database.collection('brands');

            }
            
            if (commissionModel) {
                if (data.hasOwnProperty('adminCommision')) {
                    var commission_type = data.adminCommision.type;
                    var commission_value = data.adminCommision.commission;
                    if (commission_type == "percentage") {
                        var commission_text = commission_value + '%';
                    } else {
                        if (currencyAtRight) {
                            commission_text = parseFloat(commission_value).toFixed(
                                decimal_degits) + "" + currentCurrency;
                        } else {
                            commission_text = currentCurrency + "" + parseFloat(
                                commission_value).toFixed(decimal_degits);
                        }
                    }
                    $("#admin_commision_info").show();
                    $("#admin_commision").html('Admin Commission: ' + commission_text);
                }
            }

            vendor_categories = vendor_categories.where('publish', '==', true);

            vendor_categories.get().then(async function(snapshots) {
                snapshots.docs.forEach((listval) => {
                    var data = listval.data();
                    categories_list.push(data);
                  
                });
                 database.collection('vendors').doc(vandorId).get().then(async function(snapshot) {
                        if (snapshot.exists) {
                            var data = snapshot.data();
                            var categoryIDs = []
                            categoryIDs = data.categoryID;
                            categories_list.forEach((val) => {
                                if (categoryIDs.includes(val.id)) {
                                    $('#item_category').append($("<option></option>")
                                        .attr("value", val.id)
                                        .text(val.title));
                                }
                            })
                        }
                    })
            });

            brand.get().then(async function(snapshots) {

                snapshots.docs.forEach((listval) => {
                    var data = listval.data();
                    $('#brand').append($("<option></option>")
                        .attr("value", data.id)
                        .text(data.title));
                });
            });

            jQuery(document).on("click", ".mdi-cloud-upload", function() {

                var variant = jQuery(this).data('variant');
                var photo_remove = $(this).attr('data-img');
                index = variant_photos.indexOf(photo_remove);
                if (index > -1) {
                    variant_photos.splice(index, 1); // 2nd parameter means remove one item only
                }
                var file_remove = $(this).attr('data-file');
                fileindex = variant_filename.indexOf(file_remove);
                if (fileindex > -1) {
                    variant_filename.splice(fileindex,
                        1); // 2nd parameter means remove one item only
                }
                variantindex = variant_vIds.indexOf(variant);
                if (variantindex > -1) {
                    variant_vIds.splice(variantindex,
                        1); // 2nd parameter means remove one item only
                }

                $('[id="file_' + variant + '"]').click();
            });

            jQuery(document).on("click", ".mdi-delete", function() {

                var variant = jQuery(this).data('variant');
                $('[id="variant_' + variant + '_image"]').empty();
                var photo_remove = $(this).attr('data-img');
                index = variant_photos.indexOf(photo_remove);
                if (index > -1) {
                    variant_photos.splice(index, 1); // 2nd parameter means remove one item only
                }
                var file_remove = $(this).attr('data-file');
                fileindex = variant_filename.indexOf(file_remove);
                if (fileindex > -1) {
                    variant_filename.splice(fileindex,
                        1); // 2nd parameter means remove one item only
                }
                variantindex = variant_vIds.indexOf(variant);
                if (variantindex > -1) {
                    variant_vIds.splice(variantindex,
                        1); // 2nd parameter means remove one item only
                }

            });

            jQuery(document).on("click", "#is_digital_product", function() {
                if (jQuery(this).is(':checked') && section_flag == "ecommerce-service") {
                    $("#upload_file_div").show();
                } else {
                    $("#upload_file_div").hide();
                }
            });


            var attributes = database.collection('vendor_attributes');

            attributes.get().then(async function(snapshots) {
                snapshots.docs.forEach((listval) => {
                    var data = listval.data();
                    attributes_list.push(data);
                    $('#item_attribute').append($("<option></option>")
                        .attr("value", data.id)
                        .text(data.title));
                })
                $("#item_attribute").show().chosen({
                    "placeholder_text": "{{ trans('lang.select_attribute') }}"
                });
            });

            database.collection('sections').doc(section_id).get().then(async function(snapshots) {
                var data = snapshots.data();
                if (data.serviceTypeFlag == "ecommerce-service" || data.serviceTypeFlag ==
                    "delivery-service") {
                    $("#attributes_div").show();
                    $("#item_attribute_chosen").css({
                        'width': '100%'
                    });
                } else {
                    $("#attributes_div").remove();
                    $("#attributes_div_values").remove();
                }
            });

            var digitalProductRef = database.collection('settings').doc(
                "digitalProduct");
            digitalProductRef.get().then(async function(snapshots) {
                var digitalProductData = snapshots.data();
                allowed_file_size = digitalProductData.fileSize;
                $(".max_file_size").text('{{ trans('lang.item_upload_file_max') }}' +
                    allowed_file_size + 'Mb');
            })

            $(".create_item_btn").click(async function() {
                if (parseInt(itemLimit) == -1 || parseInt(createdItem) < parseInt(itemLimit)) {
                    var id = "<?php echo uniqid(); ?>";
                    var name = $(".item_name").val();
                    var price = $(".item_price").val();
                    var wholesaleEnabled = $("#wholesale_enabled").is(':checked');
                    /* Tier one. The website reads these two, so they are still
                     * written - taken from the list rather than their own fields. */
                    var savedTiers = wholesaleTiersValue();
                    /* The store app's three-way value. Wholesale off means retail,
                     * so the dropdown only ever chooses between the other two. */
                    var saleType = wholesaleEnabled ? ($('#sale_type').val() || 'both') : 'retail';
                    var wholesalePrice = savedTiers.length ? savedTiers[0].price : '';
                    var wholesaleMinQty = savedTiers.length ? savedTiers[0].minQty : '';
                    var item_quantity = $(".item_quantity").val();
                    var discount = $(".item_discount").val();
                    var category = $("#item_category option:selected").val() || '';
                    var brand = $("#brand").val();
                    var itemCalories = parseInt($(".item_calories").val());
                    var itemGrams = parseInt($(".item_grams").val());
                    var itemProteins = parseInt($(".item_proteins").val());
                    var itemFats = parseInt($(".item_fats").val());
                    var quantity = 0;
                    var description = $("#item_description").val();
                    var itemPublish = $(".item_publish").is(":checked");
                    var nonveg = $(".item_nonveg").is(":checked");
                    var itemTakeaway = $(".item_take_away_option").is(":checked");
                    var veg = !nonveg;
                    var is_digital_product = $("#is_digital_product").is(":checked");

                    if (discount == '') {
                        discount = "0";
                    }

                    if (!itemCalories) {
                        itemCalories = 0;
                    }
                    if (!itemGrams) {
                        itemGrams = 0;
                    }
                    if (!itemFats) {
                        itemFats = 0;
                    }
                    if (!itemProteins) {
                        itemProteins = 0;
                    }
                    if (photos != '') {
                        photo = photos[0]
                    }
                    if (name == '') {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append(
                            "<p>{{ trans('lang.enter_item_name_error') }}</p>");
                        window.scrollTo(0, 0);
                    } else if (price == '') {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append(
                            "<p>{{ trans('lang.enter_item_price_error') }}</p>");
                        window.scrollTo(0, 0);
                    } else if (price <= 0) {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append(
                            "<p>{{ trans('lang.enter_positive_price_error') }}</p>");
                        window.scrollTo(0, 0);

                    } else if (item_quantity == '' || item_quantity < -1) {
                        $(".error_top").show();
                        $(".error_top").html("");
                        if (item_quantity == '') {
                            $(".error_top").append(
                                "<p>{{ trans('lang.enter_item_quantity_error') }}</p>");
                        } else {
                            $(".error_top").append(
                                "<p>{{ trans('lang.invalid_item_quantity_error') }}</p>");
                        }
                        window.scrollTo(0, 0);
                    } else if (category == '') {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append(
                            "<p>{{ trans('lang.select_item_category_error') }}</p>");
                        window.scrollTo(0, 0);
                    } else if (brand == '' && $('.brandDiv').is(':visible') == true) {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append(
                            "<p>{{ trans('lang.select_brand_error') }}</p>");
                        window.scrollTo(0, 0);
                    } else if (description == '') {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append(
                            "<p>{{ trans('lang.enter_item_description_error') }}</p>");
                        window.scrollTo(0, 0);
                    /* One check for every tier, base included - quantities rising,
                     * prices falling, each below the retail price. Replaces three
                     * separate checks that only knew about the base pair. */
                    } else if (wholesaleEnabled && wholesaleTiersError(price) !== '') {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append(
                            "<p>" + wholesaleTiersError(price) + "</p>"
                        );
                        window.scrollTo(0, 0);
                    } else if (parseInt(price) < parseInt(discount)) {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append(
                            "<p>{{ trans('lang.price_should_not_less_then_discount_error') }}</p>"
                        );
                        window.scrollTo(0, 0);

                    } else if (is_digital_product == true && digital_product_file == '') {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append(
                            "<p>{{ trans('lang.upload_digital_file_error') }}</p>");
                        window.scrollTo(0, 0);

                    } else {

                        //start-item attribute
                        var attributes = [];
                        var variants = [];

                        if ($('#attributes').val().length > 0) {
                            var attributes = $.parseJSON($('#attributes').val());
                        }
                        if ($('#variants').val().length > 0) {
                            var variantsSet = $.parseJSON($('#variants').val());
                            var isValid = false; // Flag to track validation
                            await storeVariantImageData().then(async (vIMG) => {

                                $.each(variantsSet, function(key, variant) {
                                    var variant_id = uniqid();
                                    var variant_sku = variant;
                                    var variant_price = $('#price_' +
                                        variant).val();
                                    var variant_quantity = $('#qty_' +
                                        variant).val();
                                    var variant_image = $('#variant_' +
                                        variant + '_url').val();
                                    var variant_wholesale_price = wholesaleEnabled ?
                                        $('#wholesale_price_' + variant).val() : '';

                                    // Validation for variant_price
                                    if (!variant_price || parseFloat(
                                            variant_price) <= 0) {
                                        $(".error_top").show();
                                        $(".error_top").html("");
                                        $(".error_top").append(
                                            "<p>{{ trans('lang.enter_positive_variant_price_error') }}</p>"
                                        );
                                        window.scrollTo(0, 0);
                                        isValid = true;
                                        return false; // Exit loop
                                    }

                                    if (wholesaleEnabled) {
                                        if (!variant_wholesale_price || parseFloat(variant_wholesale_price) <= 0) {
                                            $(".error_top").show();
                                            $(".error_top").html("");
                                            $(".error_top").append(
                                                "<p>{{ trans('lang.enter_positive_variant_wholesale_price_error') }}</p>"
                                            );
                                            window.scrollTo(0, 0);
                                            isValid = true;
                                            return false; // Exit loop
                                        }
                                        if (parseFloat(variant_wholesale_price) >= parseFloat(variant_price)) {
                                            $(".error_top").show();
                                            $(".error_top").html("");
                                            $(".error_top").append(
                                                "<p>{{ trans('lang.variant_wholesale_price_less_than_price_error') }}</p>"
                                            );
                                            window.scrollTo(0, 0);
                                            isValid = true;
                                            return false; // Exit loop
                                        }
                                    }

                                    variants.push({
                                        'variant_id': variant_id,
                                        'variant_sku': variant_sku,
                                        'variant_price': variant_price,
                                        'variant_quantity': variant_quantity,
                                        'variant_image': variant_image,
                                        'variant_wholesale_price': variant_wholesale_price
                                    });
                                });

                            }).catch(err => {
                                jQuery("#data-table_processing").hide();
                                $(".error_top").show();
                                $(".error_top").html("");
                                $(".error_top").append("<p>" + err + "</p>");
                                window.scrollTo(0, 0);
                            });
                            if (isValid) {
                                return;

                            }
                            $(".error_top").hide().html("");

                        }

                        var item_attribute = null;
                        if (attributes.length > 0 && variants.length > 0) {
                            var item_attribute = {
                                'attributes': attributes,
                                'variants': variants
                            };
                        }
                        //end-item attribute
                        let selectedTaxes = [];
                        $('#taxes option:selected').each(function() {
                            let taxData = $(this).attr('data-tax');
                            if (taxData) {
                                selectedTaxes.push(JSON.parse(decodeURIComponent(taxData)));
                            }
                        });
                        jQuery("#data-table_processing").show();
                        await storeDigitalImageData().then(async (DigitalImg) => {
                            await storeProductImageData().then(async (IMG) => {
                                if (IMG.length > 0) {
                                    photo = IMG[0];
                                }
                                database.collection('vendor_products')
                                    .doc(id).set({
                                        'name': name,
                                        'price': price,
                                        'quantity': parseInt(
                                            item_quantity),
                                        'disPrice': discount,
                                        'wholesaleEnabled': wholesaleEnabled,
                                        'saleType': saleType,
                                        'wholesalePrice': wholesaleEnabled ? wholesalePrice : '',
                                        'wholesaleMinQty': wholesaleEnabled ? wholesaleMinQty : '',
                                        /* Cleared with the toggle, like the price and quantity, so a
                                         * product no longer sold wholesale does not keep notes
                                         * about it. */
                                        'wholesaleDetails': wholesaleEnabled ? wholesaleDetailsValue() : '',
                                        /* Written alongside the pair above, not instead of it. The
                                         * store app reads this array; the website still reads the
                                         * pair, which is tier one. */
                                        'wholesaleTiers': wholesaleEnabled
                                            ? savedTiers : [],
                                        'vendorID': (authRole === 'vendor') ? vandorId : empVendorId,
                                        'categoryID': category,
                                        'brandID': brand,
                                        'photo': photo,
                                        'photos': IMG,
                                        'calories': itemCalories,
                                        "grams": itemGrams,
                                        'proteins': itemProteins,
                                        'fats': itemFats,
                                        'description': description,
                                        'publish': itemPublish,
                                        'section_id': section_id,
                                        'nonveg': nonveg,
                                        'veg': veg,
                                        'addOnsTitle': addOnesTitle,
                                        'addOnsPrice': addOnesPrice,
                                        'takeawayOption': itemTakeaway,
                                        'id': id,
                                        'item_attribute': item_attribute,
                                        'product_specification': product_specification,
                                        'isDigitalProduct': is_digital_product,
                                        'digitalProduct': DigitalImg,
                                        'createdAt': firebase.firestore.FieldValue.serverTimestamp() ,
                                        'taxSetting': selectedTaxes,
                                    }).then(function(result) {
                                        window.location.href =
                                            '{{ route('items') }}';
                                    });
                            }).catch(err => {
                                jQuery("#data-table_processing").hide();
                                $(".error_top").show();
                                $(".error_top").html("");
                                $(".error_top").append("<p>" + err +
                                    "</p>");
                                window.scrollTo(0, 0);
                            });
                        }).catch(err => {
                            jQuery("#data-table_processing").hide();
                            $(".error_top").show();
                            $(".error_top").html("");
                            $(".error_top").append("<p>" + err + "</p>");
                            window.scrollTo(0, 0);
                        });
                    }
                } else {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append(
                        "<p>{{ trans('lang.create_item_limit_exceed') }}</p>"
                    );
                    window.scrollTo(0, 0);
                }

            })



        })


        var storageRef = firebase.storage().ref('images');

        function handleFileSelectProduct(evt) {
            var f = evt.target.files[0];
            var reader = new FileReader();
            reader.onload = (function(theFile) {
                return function(e) {

                    var filePayload = e.target.result;
                    var hash = CryptoJS.SHA256(Math.random() + CryptoJS.SHA256(filePayload));
                    var val = f.name;
                    var ext = val.split('.')[1];
                    var docName = val.split('fakepath')[1];
                    var filename = (f.name).replace(/C:\\fakepath\\/i, '')

                    var timestamp = Number(new Date());
                    var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                    product_image_filename.push(filename);
                    productImagesCount++;
                    photos_html = '<span class="image-item" id="photo_' + productImagesCount +
                        '"><span class="remove-btn" data-id="' + productImagesCount + '" data-img="' +
                        filePayload +
                        '"><i class="fa fa-remove"></i></span><img class="rounded" width="50px" id="" height="auto" src="' +
                        filePayload + '"></span>'
                    $(".product_image").append(photos_html);
                    photos.push(filePayload);
                    $("#product_image").val('');

                };
            })(f);
            reader.readAsDataURL(f);
        }

        $(document).on("click", ".remove-btn", function() {
            var id = $(this).attr('data-id');
            var photo_remove = $(this).attr('data-img');
            $("#photo_" + id).remove();
            index = photos.indexOf(photo_remove);
            if (index > -1) {
                photos.splice(index, 1); // 2nd parameter means remove one item only
            }

        });

        function handleVariantFileSelect(evt, vid) {
            var f = evt.target.files[0];
            var reader = new FileReader();

            reader.onload = (function(theFile) {
                return function(e) {

                    var filePayload = e.target.result;
                    var hash = CryptoJS.SHA256(Math.random() + CryptoJS.SHA256(filePayload));
                    var val = f.name;
                    var ext = val.split('.')[1];
                    var docName = val.split('fakepath')[1];
                    var timestamp = Number(new Date());
                    var filename = (f.name).replace(/C:\\fakepath\\/i, '')
                    var filename = 'variant_' + vid + '_' + timestamp + '.' + ext;
                    variant_filename.push(filename);
                    variant_photos.push(filePayload);
                    variant_vIds.push(vid);
                    $('[id="variant_' + vid + '_image"]').empty();
                    $('[id="variant_' + vid + '_image"]').html('<img class="rounded" style="width:50px" src="' +
                        filePayload + '" alt="image"><i class="mdi mdi-delete" data-variant="' + vid +
                        '" data-img="' + filePayload + '" data-file="' + filename + '"></i>');
                    $('#upload_' + vid).attr('data-img', filePayload);
                    $('#upload_' + vid).attr('data-file', filename);

                };
            })(f);
            reader.readAsDataURL(f);
        }

        function handleZipUpload(evt) {
            var f = evt.target.files[0];
            var reader = new FileReader();

            reader.onload = (function(theFile) {
                return function(e) {

                    var filePayload = e.target.result;

                    var hash = CryptoJS.SHA256(Math.random() + CryptoJS.SHA256(filePayload));
                    var val = f.name;
                    var ext = val.split('.')[1];
                    var size = f.size;

                    var max_file_size = parseInt(allowed_file_size) * 1000000;
                    if (size > max_file_size) {
                        $("#digital_product_file").val('');
                        alert('{{ trans('lang.max_file_limit_error') }}' + allowed_file_size + 'Mb');
                        return false;
                    }

                    if (ext == "jpg" || ext == "jpeg" || ext == "png" || ext == "gif" || ext == "zip" || ext ==
                        "pdf") {

                        var docName = val.split('fakepath')[1];
                        var filename = (f.name).replace(/C:\\fakepath\\/i, '')

                        var timestamp = Number(new Date());
                        var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                        digital_product_file_name = filename;
                        digital_product_file = filePayload;
                        if (ext == "zip") {
                            digital_product_ext = 'zip';
                            $("#uploding_zip").html(
                                '<span class="image-item zip-file"><span class=""   data-file="' +
                                filePayload + '"></span><a href="' + filePayload +
                                '" download><i class="fa fa-file-text" style="font-size:45px"></i></a></span>'
                            );
                        } else if (ext == 'pdf') {
                            digital_product_ext = 'pdf';
                            $("#uploding_zip").html(
                                '<span class="image-item zip-file"><span class=""   data-file="' +
                                filePayload + '"></span><a href="' + filePayload +
                                '" target="_blank"><i class="fa fa-file-text" style="font-size:45px"></i></a></span>'
                            );
                        } else {
                            digital_product_ext = 'image';
                            $("#uploding_zip").html(
                                '<span class="image-item zip-file"><span class=""  data-file="' +
                                filePayload + '"></span><img width="100px" id="" height="auto" src="' +
                                filePayload + '"></span>');
                        }
                        $("#digital_product_file").val('');


                    } else {
                        $("#digital_product_file").val('');
                        alert('{{ trans('lang.enter_valid_file_ext') }}')
                        return false;
                    }
                };
            })(f);
            reader.readAsDataURL(f);
        }

        function addOneFunction() {
            $("#add_ones_div").show();
            $(".save_add_one_btn").show();
        }

        function saveAddOneFunction() {
            var optiontitle = $(".add_ons_title").val();
            var optionPrice = $(".add_ons_price").val();
            $(".add_ons_price").val('');
            $(".add_ons_title").val('');
            if (optiontitle != '' && optionPrice != '') {
                addOnesPrice.push(optionPrice);
                addOnesTitle.push(optiontitle);
                var index = addOnesTitle.length - 1;
                $(".add_ons_list").append('<div class="row" style="margin-top:5px;" id="add_ones_list_iteam_' + index +
                    '"><div class="col-5"><input class="form-control" type="text" value="' + optiontitle +
                    '" disabled ></div><div class="col-5"><input class="form-control" type="text" value="' +
                    optionPrice +
                    '" disabled ></div><div class="col-2"><button class="btn" type="button" onclick="deleteAddOnesSingle(' +
                    index + ')"><span class="fa fa-trash"></span></button></div></div>');
            } else {
                alert("Please enter Title and Price");
            }
        }

        function deleteAddOnesSingle(index) {
            addOnesTitle.splice(index, 1);
            addOnesPrice.splice(index, 1);
            $("#add_ones_list_iteam_" + index).hide();
        }

        function addProductSpecificationFunction() {
            $("#add_product_specification_div").show();
            $(".save_product_specification_btn").show();
        }

        function saveProductSpecificationFunction() {
            var optionlabel = $(".add_label").val();
            var optionvalue = $(".add_value").val();
            $(".add_label").val('');
            $(".add_value").val('');
            if (optionlabel != '' && optionlabel != '') {

                product_specification[optionlabel] = optionvalue;

                $(".product_specification").append(
                    '<div class="row" style="margin-top:5px;" id="add_product_specification_iteam_' + optionlabel +
                    '"><div class="col-5"><input class="form-control" type="text" value="' + optionlabel +
                    '" disabled ></div><div class="col-5"><input class="form-control" type="text" value="' +
                    optionvalue +
                    '" disabled ></div><div class="col-2"><button class="btn" type="button" onclick=deleteProductSpecificationSingle("' +
                    optionlabel + '")><span class="fa fa-trash"></span></button></div></div>');
            } else {
                alert("Please enter Label and Value");
            }
        }

        function deleteProductSpecificationSingle(index) {

            delete product_specification[index];
            $("#add_product_specification_iteam_" + index).hide();
        }

        function selectAttribute() {
            var html = '';
            $("#item_attribute").find('option:selected').each(function() {
                html += '<div class="row">';
                html += '<div class="col-md-3">';
                html += '<label>' + $(this).text() + '</label>';
                html += '</div>';
                html += '<div class="col-lg-9">';
                html += '<input type="text" class="form-control" id="attribute_options_' + $(this).val() +
                    '" placeholder="Add attribute values" data-role="tagsinput" onchange="variants_update()">';
                html += '</div>';
                html += '</div>';
            });
            $("#item_attributes").html(html);
            $("#item_attributes input[data-role=tagsinput]").tagsinput();
            $("#attributes").val('');
            $("#variants").val('');
            $("#item_variants").html('');
        }


        /* The variant table is rebuilt so its wholesale column appears and
         * disappears with the toggle, rather than being drawn once at load. */
        /* The wholesale column is always drawn and shown or hidden with the
         * toggle, so a price already typed into it survives being switched off
         * and on again. */


        /* ---- Wholesale price tiers ----
         *
         * Shape comes from the store app: `wholesaleTiers: [{minQty, price}]`,
         * at most five, sorted by quantity. Tier one is in this list like any
         * other row - the separate Wholesale Price and Minimum Quantity fields
         * are gone, so a vendor sees the same thing here as in the app.
         *
         * The legacy `wholesalePrice` + `wholesaleMinQty` pair is still written
         * from tier one, because the website prices from it and knows nothing
         * about the array.
         *
         * The array is the truth and the inputs are redrawn from it, so removing
         * a middle row cannot leave the rest holding stale positions. */
        var wholesaleTiers = [];
        var MAX_WHOLESALE_TIERS = 5;

        function renderWholesaleTiers() {
            var container = document.getElementById('wholesale_tiers');

            if (!container) {
                return;
            }

            container.innerHTML = '';

            if (wholesaleTiers.length === 0) {
                $('#add_wholesale_tier').prop('disabled', false);
                return;
            }

            /* A heading row above the list rather than a label per row, which
             * would repeat five times. The spacers keep it lined up with the
             * number badge and the remove button. */
            var head = document.createElement('div');
            head.className = 'd-flex align-items-center';

            var headNumber = document.createElement('span');
            headNumber.className = 'badge mr-2';
            headNumber.style.visibility = 'hidden';
            headNumber.textContent = '0';

            var headQty = document.createElement('label');
            headQty.className = 'control-label mr-2 mb-1';
            headQty.style.flex = '1 1 0';
            headQty.textContent = "{{ trans('lang.tier_from_quantity') }}";

            var headPrice = document.createElement('label');
            headPrice.className = 'control-label mr-2 mb-1';
            headPrice.style.flex = '1 1 0';
            headPrice.textContent = "{{ trans('lang.tier_unit_price') }}";

            var headRemove = document.createElement('span');
            headRemove.style.visibility = 'hidden';
            headRemove.className = 'btn btn-danger';
            headRemove.innerHTML = '<i class="mdi mdi-delete"></i>';

            head.appendChild(headNumber);
            head.appendChild(headQty);
            head.appendChild(headPrice);
            head.appendChild(headRemove);
            container.appendChild(head);

            wholesaleTiers.forEach(function (tier, index) {
                var row = document.createElement('div');
                row.className = 'form-group d-flex align-items-center mt-1';

                var number = document.createElement('span');
                number.className = 'badge badge-primary mr-2';
                number.textContent = index + 1;

                var qty = document.createElement('input');
                qty.type = 'number';
                qty.min = '2';
                qty.className = 'form-control mr-2';
                qty.style.flex = '1 1 0';
                qty.placeholder = "0";
                qty.value = tier.minQty;
                qty.addEventListener('input', function () {
                    wholesaleTiers[index].minQty = this.value;
                });

                var price = document.createElement('input');
                price.type = 'number';
                price.min = '0';
                price.className = 'form-control mr-2';
                price.style.flex = '1 1 0';
                price.placeholder = "0";
                price.value = tier.price;
                price.addEventListener('input', function () {
                    wholesaleTiers[index].price = this.value;
                });

                row.appendChild(number);
                row.appendChild(qty);
                row.appendChild(price);

                /* Tier one is the base every other tier is measured against, and
                 * the website reads it, so it cannot be removed while wholesale
                 * is switched on. Unticking wholesale clears the lot.
                 *
                 * It still gets an invisible button in that slot, or its two
                 * inputs would be wider than every row below it. */
                var remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'btn btn-danger';
                remove.innerHTML = '<i class="mdi mdi-delete"></i>';

                if (index === 0) {
                    remove.style.visibility = 'hidden';
                    remove.disabled = true;
                } else {
                    remove.addEventListener('click', function () {
                        wholesaleTiers.splice(index, 1);
                        renderWholesaleTiers();
                    });
                }

                row.appendChild(remove);
                container.appendChild(row);
            });

            $('#add_wholesale_tier').prop('disabled', wholesaleTiers.length >= MAX_WHOLESALE_TIERS);
        }

        /* Switching wholesale on with nothing there gives tier one straight away,
         * so a vendor is never looking at an empty section wondering where to
         * type. */
        function ensureFirstWholesaleTier() {
            if (wholesaleTiers.length === 0) {
                wholesaleTiers.push({ minQty: '', price: '' });
            }

            renderWholesaleTiers();
        }

        $(document).on('click', '#add_wholesale_tier', function () {
            if (wholesaleTiers.length >= MAX_WHOLESALE_TIERS) {
                return;
            }

            wholesaleTiers.push({ minQty: '', price: '' });
            renderWholesaleTiers();
        });

        /* Every tier, sorted, with rows left entirely blank dropped - adding a
         * row and changing your mind should not stop the product saving. */
        function wholesaleTiersValue() {
            var tiers = [];

            wholesaleTiers.forEach(function (tier) {
                if (String(tier.minQty).trim() === '' && String(tier.price).trim() === '') {
                    return;
                }

                tiers.push({
                    'minQty': parseInt(tier.minQty),
                    'price': parseFloat(tier.price)
                });
            });

            tiers.sort(function (a, b) {
                return a.minQty - b.minQty;
            });

            return tiers;
        }

        /* The app's rules: quantities strictly increasing, prices strictly
         * decreasing, every quantity at least 2, every price below the retail
         * price. Returns an error message, or '' when the tiers are sound. */
        function wholesaleTiersError(retailPrice) {
            var tiers = wholesaleTiersValue();

            if (tiers.length === 0) {
                return "{{ trans('lang.wholesale_tier_required_error') }}";
            }

            if (tiers.length > MAX_WHOLESALE_TIERS) {
                return "{{ trans('lang.wholesale_tier_max_error') }}";
            }

            for (var i = 0; i < tiers.length; i++) {
                if (isNaN(tiers[i].minQty) || tiers[i].minQty < 2) {
                    return "{{ trans('lang.wholesale_min_qty_error') }}";
                }

                if (isNaN(tiers[i].price) || tiers[i].price <= 0) {
                    return "{{ trans('lang.wholesale_price_positive_error') }}";
                }

                if (tiers[i].price >= parseFloat(retailPrice)) {
                    return "{{ trans('lang.wholesale_price_less_than_price_error') }}";
                }

                if (i > 0) {
                    if (tiers[i].minQty <= tiers[i - 1].minQty) {
                        return "{{ trans('lang.wholesale_tier_qty_order_error') }}";
                    }

                    if (tiers[i].price >= tiers[i - 1].price) {
                        return "{{ trans('lang.wholesale_tier_price_order_error') }}";
                    }
                }
            }

            return '';
        }

        /* Editing. A product saved by the store app carries the array; one saved
         * by this panel before tiers existed carries only the old pair, which
         * becomes tier one. */
        function setWholesaleTiers(tiers, legacyPrice, legacyMinQty) {
            wholesaleTiers = [];

            if (Array.isArray(tiers) && tiers.length > 0) {
                tiers.forEach(function (tier) {
                    wholesaleTiers.push({
                        minQty: tier.minQty !== undefined ? tier.minQty : '',
                        price: tier.price !== undefined ? tier.price : ''
                    });
                });
            } else if (legacyPrice || legacyMinQty) {
                wholesaleTiers.push({
                    minQty: legacyMinQty !== undefined ? legacyMinQty : '',
                    price: legacyPrice !== undefined ? legacyPrice : ''
                });
            }

            renderWholesaleTiers();
        }

        /* The wholesale notes editor.
         *
         * Same toolbar as the Terms and Conditions editor in the admin panel, so
         * the two look alike. It lives inside `.wholesale_fields`, which is shown
         * and hidden by the toggle above, so it needs no visibility handling of
         * its own.
         *
         * Built once on ready rather than each time the toggle is switched on -
         * rebuilding it would throw away whatever the vendor had typed. */
        function initWholesaleEditor() {
            /* Nothing to build, or summernote already built it. Looks for the
             * editor anywhere beside the textarea rather than strictly next to
             * it, so the check does not depend on where summernote inserts. */
            if (!$('#wholesale_details').length || $('#wholesale_details').siblings('.note-editor').length) {
                return;
            }

            /* The script tag is at the top of this section, so this should never
             * happen - but a failed asset would otherwise throw here and take
             * the rest of the caller down with it. */
            if (typeof $.fn.summernote !== 'function') {
                console.error('summernote did not load - the wholesale notes editor cannot be built');
                return;
            }

            $('#wholesale_details').summernote({
                /* Matches the Description box below it - that is rows="8", which
                 * comes out around 200px. */
                height: 200,
                width: '100%',
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['forecolor', ['forecolor']],
                    ['backcolor', ['backcolor']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['height', ['height']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        }

        /* Empty when the vendor has typed nothing - summernote leaves an empty
         * paragraph behind, which would otherwise be stored as content. */
        function wholesaleDetailsValue() {
            if (!$('#wholesale_details').length) {
                return '';
            }

            /* Read the plain textarea when the editor was never built, so a save
             * still works rather than throwing on a missing summernote. */
            if (!$('#wholesale_details').siblings('.note-editor').length) {
                return $('#wholesale_details').val().trim();
            }

            var html = $('#wholesale_details').summernote('code');

            return $('<div>').html(html).text().trim() === '' ? '' : html;
        }

        function applyWholesaleVisibility() {
            if ($('#wholesale_enabled').is(':checked')) {
                $('.wholesale_fields').show();
                $('.wholesale_column').show();
                /* Built here as well as on ready, and deliberately AFTER show().
                 * Summernote measures the textarea when it is created, so
                 * building it while `.wholesale_fields` is still display:none
                 * left a collapsed box; and if anything earlier in the ready
                 * queue throws, the ready call never happens at all. The guard
                 * inside makes the second call a no-op, so nothing typed is
                 * lost. */
                initWholesaleEditor();
                ensureFirstWholesaleTier();
            } else {
                $('.wholesale_fields').hide();
                $('.wholesale_column').hide();
            }
        }

        $(document).on('change', '#wholesale_enabled', applyWholesaleVisibility);

        function variants_update() {
            var html = '';
            variant_photos = [];
            variant_vIds = [];
            variant_filename = [];
            var item_attribute = $("#item_attribute").map(function(idx, ele) {
                return $(ele).val();
            }).get();

            if (item_attribute.length > 0) {

                var attributes = [];
                var attributeSet = [];
                $.each(item_attribute, function(index, attribute) {
                    var attribute_options = $("#attribute_options_" + attribute).val();
                    if (attribute_options) {
                        var attribute_options = attribute_options.split(',');
                        attribute_options = $.map(attribute_options, function(value) {
                            return value.replace(/[^a-zA-Z0-9]/g, '');
                        });
                        attributeSet.push(attribute_options);
                        attributes.push({
                            'attribute_id': attribute,
                            'attribute_options': attribute_options
                        });
                    }
                });

                if (attributeSet.length > 0) {

                    $('#attributes').val(JSON.stringify(attributes));

                    var variants = getCombinations(attributeSet);
                    $('#variants').val(JSON.stringify(variants));

                    html += '<table class="table table-bordered">';
                    html += '<thead class="thead-light">';
                    html += '<tr>';
                    html += '<th class="text-center"><span class="control-label">{{ trans('lang.variant') }}</span></th>';
                    html += '<th class="text-center"><span class="control-label">{{ trans('lang.variant_price') }}</span></th>';
                    html += '<th class="text-center wholesale_column"><span class="control-label">{{ trans('lang.variant_wholesale_price') }}</span></th>';
                    html += '<th class="text-center"><span class="control-label">{{ trans('lang.variant_quantity') }}</span></th>';
                    html += '<th class="text-center"><span class="control-label">{{ trans('lang.variant_image') }}</span></th>';
                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';
                    $.each(variants, function(index, variant) {
                        html += '<tr>';
                        html += '<td><label for="" class="control-label">' + variant + '</label></td>';
                        html += '<td>';
                        var check_variant_price = $('#price_' + variant).val() ? $('#price_' + variant).val() : 1;
                        html += '<input type="number" id="price_' + variant + '" value="' + check_variant_price +
                            '" min="0" class="form-control">';
                        html += '</td>';
                        var check_variant_wholesale = $('#wholesale_price_' + variant).val() ? $('#wholesale_price_' + variant).val() : '';
                        html += '<td class="wholesale_column">';
                        html += '<input type="number" id="wholesale_price_' + variant + '" value="' + check_variant_wholesale +
                            '" min="0" class="form-control">';
                        html += '</td>';
                        html += '<td>';
                        var check_variant_qty = $('#price_' + variant).val() ? $('#price_' + variant).val() : -1;
                        html += '<input type="number" id="qty_' + variant + '" value="' + check_variant_qty +
                            '" min="-1" class="form-control">';
                        html += '</td>';
                        html += '<td>';
                        html += '<div class="variant-image">';
                        html += '<div class="upload">';
                        html += '<div class="image" id="variant_' + variant + '_image"></div>';
                        html += '<div class="icon"><i class="mdi mdi-cloud-upload" data-variant="' + variant +
                            '"></i></div>';
                        html += '</div>';
                        html += '<div id="variant_' + variant + '_process"></div>';
                        html += '<div class="input-file">';
                        html += '<input type="file" id="file_' + variant +
                            '" onChange="handleVariantFileSelect(event,\'' + variant +
                            '\')" class="form-control" style="display:none;">';
                        html += '<input type="hidden" id="variant_' + variant + '_url" value="">';
                        html += '</div>';
                        html += '</div>';
                        html += '</td>';
                        html += '</tr>';
                    });
                    html += '</tbody>';
                    html += '</table>';
                }
            }
            $("#item_variants").html(html);
            initWholesaleEditor();
            applyWholesaleVisibility();
        }

        function getCombinations(arr) {
            if (arr.length) {
                if (arr.length == 1) {
                    return arr[0];
                } else {
                    var result = [];
                    var allCasesOfRest = getCombinations(arr.slice(1));
                    for (var i = 0; i < allCasesOfRest.length; i++) {
                        for (var j = 0; j < arr[0].length; j++) {
                            result.push(arr[0][j] + '-' + allCasesOfRest[i]);
                        }
                    }
                    return result;
                }
            }
        }

        async function storeProductImageData() {
            var newPhoto = [];
            if (photos.length > 0) {
                await Promise.all(photos.map(async (productPhoto, index) => {
                    productPhoto = productPhoto.replace(/^data:image\/[a-z]+;base64,/, "");
                    var uploadTask = await storageRef.child(product_image_filename[index]).putString(
                        productPhoto, 'base64', {
                            contentType: 'image/jpg'
                        });
                    var downloadURL = await uploadTask.ref.getDownloadURL();
                    newPhoto.push(downloadURL);
                }));
            }
            return newPhoto;
        }

        async function storeDigitalImageData() {
            var newPhoto = '';
            try {
                if (digital_product_file != '') {
                    digital_product_file = digital_product_file.replace(/^data:image\/[a-z]+;base64,/, "")
                    if (digital_product_ext == 'zip' || digital_product_ext == "pdf") {
                        var uploadTask = await storageRef.child(digital_product_file_name).put(digital_product_file);
                    } else {
                        var uploadTask = await storageRef.child(digital_product_file_name).putString(
                            digital_product_file, 'base64', {
                                contentType: 'image/jpg'
                            });
                    }
                    var downloadURL = await uploadTask.ref.getDownloadURL();
                    newPhoto = downloadURL;
                    digital_product_file = downloadURL;
                }
            } catch (error) {
                console.log("ERR ===", error);
            }
            return newPhoto;
        }

        function uniqid(prefix = "", random = false) {
            const sec = Date.now() * 1000 + Math.random() * 1000;
            const id = sec.toString(16).replace(/\./g, "").padEnd(14, "0");
            return `${prefix}${id}${random ? `.${Math.trunc(Math.random() * 100000000)}` : ""}`;
        }

        async function storeVariantImageData() {
            var newPhoto = [];
            if (variant_photos.length > 0) {
                await Promise.all(variant_photos.map(async (variantPhoto, index) => {
                    variantPhoto = variantPhoto.replace(/^data:image\/[a-z]+;base64,/, "");
                    var uploadTask = await storageRef.child(variant_filename[index]).putString(
                        variantPhoto, 'base64', {
                            contentType: 'image/jpg'
                        });
                    var downloadURL = await uploadTask.ref.getDownloadURL();
                    $('[id="variant_' + variant_vIds[index] + '_url"]').val(downloadURL);
                    newPhoto.push(downloadURL);
                }));
            }
            return newPhoto;
        }
        // Clear error message when user updates the price field
        $(document).on('input', '[id^="price_"]', function() {
            if (parseFloat($(this).val()) > 0) {
                $(".error_top").hide().html(""); // Hide the error message
            }
        });
    </script>
@endsection
