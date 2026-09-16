{{--
    The store form, shared by create and edit so the two cannot drift.

    Lifted verbatim out of users/profile.blade.php, where it lived inside a
    `vendor_fieldset` on the user profile screen. The markup is unchanged; only
    its home has moved.

    The accompanying behaviour is in form_scripts.blade.php.
--}}
                    <fieldset>
                        <legend>{{ trans('lang.vendor_details') }}</legend>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{ trans('lang.vendor_name') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control vendor_name">
                                <div class="form-text text-muted">
                                    {{ trans('lang.vendor_name_help') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{ trans('lang.wallet_amount') }}</label>
                            <h5 class="col-3 control-label text-primary user_wallet"><a href="#"></a></h5>
                        </div>
                        <div class="form-group row">
                            <label class="col-3 control-label ">{{ trans('lang.select_section') }}</label>
                            <div class="col-9">
                                <select name="section_id" id="section_id" class="form-control">
                                    <option value="">{{ trans('lang.select') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-3 control-label">{{ trans('lang.category_plural') }}</label>
                            <div class="col-7">
                                <select id='vendor_cuisines' class="form-control chosen-select" multiple="multiple">
                                </select>
                                <div class="form-text text-muted">
                                    {{ trans('lang.vendor_category_help') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-3 control-label">{{ trans('lang.vendor_phone') }}</label>
                            <div class="col-9">
                                <input type="text" class="form-control vendor_phone" onkeypress="return chkAlphabets2(event,'error4')">
                                <div id="error4" class="err"></div>
                                <div class="form-text text-muted">
                                    {{ trans('lang.vendor_phone_help') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-3 control-label">{{ trans('lang.region') }}<span class="required-field"></span></label>
                            <div class="col-9">
                                <select id='region_id' class="form-control">
                                    <option value="">{{ trans('lang.select_region') }}</option>
                                </select>
                                <div class="form-text text-muted">
                                    {{ trans('lang.region_help') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-3 control-label">{{ trans('lang.zone') }}<span class="required-field"></span></label>
                            <div class="col-9">
                                <select id='zone' class="form-control">
                                    <option value="">{{ trans('lang.select_zone') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-3 control-label">{{ trans('lang.vendor_address') }}</label>
                            <div class="col-9">
                                <input type="text" class="form-control vendor_address">
                                <div class="form-text text-muted">
                                    {{ trans('lang.vendor_address_help') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-9">
                                <h6>{{ trans('lang.cordinates') }} <a target="_blank" href="https://www.latlong.net/"></a>{{ trans('lang.lat_long') }}
                                </h6>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-3 control-label">{{ trans('lang.vendor_latitude') }}</label>
                            <div class="col-9">
                                <input type="text" class="form-control vendor_latitude">
                                <div class="form-text text-muted">
                                    {{ trans('lang.vendor_latitude_help') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-3 control-label">{{ trans('lang.vendor_longitude') }}</label>
                            <div class="col-9">
                                <input type="text" class="form-control vendor_longitude">
                                <div class="form-text text-muted">
                                    {{ trans('lang.vendor_longitude_help') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-3 control-label ">{{ trans('lang.vendor_description') }}</label>
                            <div class="col-7">
                                <textarea rows="7" class="vendor_description form-control" id="vendor_description"></textarea>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset style="display:none;" id="showhidedinein">
                        <legend>{{ trans('lang.dine-in-feature') }}</legend>
                        <div class="form-group row">
                            <div class="form-group row width-50">
                                <div class="form-check width-100">
                                    <input type="checkbox" id="dine_in_feature" class="">
                                    <label class="col-3 control-label" for="dine_in_feature">{{ trans('lang.dine-in-feature') }}</label>
                                </div>
                            </div>                                           
                            <div class="divein_div" style="display:none">
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.Opening_Time') }}</label>
                                    <div class="col-7">
                                        <input type="time" class="form-control" id="openDineTime" required>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.Closing_Time') }}</label>
                                    <div class="col-7">
                                        <input type="time" class="form-control" id="closeDineTime" required>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.cost') }}</label>
                                    <div class="col-7">
                                        <input type="number" class="form-control vendor_cost" required>
                                    </div>
                                </div>
                                <div class="form-group row width-100 vendor_image">
                                    <label class="col-3 control-label">{{ trans('lang.menu_card') }}</label>
                                    <div class="col-7">
                                        <div id="photos_menu_card"></div>
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-3 control-label"></label> <!-- empty label for alignment -->
                                    <div class="col-7">
                                        <input type="file" onChange="handleFileSelectMenuCard(event)">
                                        <div id="uploaded_image_menu"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>{{ trans('lang.gallery') }}</legend>
                        <div class="form-group row width-50 vendor_image">
                            <div class="">
                                <div id="photos"></div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div>
                                <input type="file" onChange="handleFileSelect(event,'photos')">
                                <div id="uploding_image_photos"></div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset id="working_hour_section" class="d-none">
                        <legend>{{ trans('lang.working_hours') }}</legend>
                        <div class="form-group row">
                            <label class="col-12 control-label" style="color:red;font-size:15px;">{{ trans('lang.working_hour_note') }}</label>
                            <div class="form-group row width-100">
                                <div class="col-7">
                                    <button type="button" class="btn btn-primary  add_working_hours_restaurant_btn">
                                        <i></i>{{ trans('lang.add_working_hours') }}
                                    </button>
                                </div>
                            </div>
                            <div class="working_hours_div" style="display:none">
                                <div class="form-group row">
                                    <label class="col-1 control-label">{{ trans('lang.sunday') }}</label>
                                    <div class="col-12">
                                        <button type="button" class="btn btn-primary add_more_sunday" onclick="addMorehour('Sunday','sunday', '1')">
                                            {{ trans('lang.add_more') }}
                                        </button>
                                    </div>
                                </div>
                                <div class="restaurant_discount_options_Sunday_div restaurant_discount" style="display:none">
                                    <table class="booking-table" id="working_hour_table_Sunday">
                                        <tr>
                                            <th>
                                                <label class="col-3 control-label">{{ trans('lang.from') }}</label>
                                            </th>
                                            <th>
                                                <label class="col-3 control-label">{{ trans('lang.to') }}</label>
                                            </th>
                                            <th>
                                                <label class="col-3 control-label">{{ trans('lang.actions') }}</label>
                                            </th>
                                        </tr>
                                    </table>
                                </div>
                                <div class="form-group row">
                                    <label class="col-1 control-label">{{ trans('lang.monday') }}</label>
                                    <div class="col-12">
                                        <button type="button" class="btn btn-primary add_more_sunday" onclick="addMorehour('Monday','monday', '1')">
                                            {{ trans('lang.add_more') }}
                                        </button>
                                    </div>
                                </div>
                                <div class="restaurant_discount_options_Monday_div restaurant_discount" style="display:none">
                                    <table class="booking-table" id="working_hour_table_Monday">
                                        <tr>
                                            <th>
                                                <label class="col-3 control-label">{{ trans('lang.from') }}</label>
                                            </th>
                                            <th>
                                                <label class="col-3 control-label">{{ trans('lang.to') }}</label>
                                            </th>
                                            <th>
                                                <label class="col-3 control-label">{{ trans('lang.actions') }}</label>
                                            </th>
                                        </tr>
                                    </table>
                                </div>
                                <div class="form-group row">
                                    <label class="col-1 control-label">{{ trans('lang.tuesday') }}</label>
                                    <div class="col-12">
                                        <button type="button" class="btn btn-primary" onclick="addMorehour('Tuesday','tuesday', '1')">
                                            {{ trans('lang.add_more') }}
                                        </button>
                                    </div>
                                </div>
                                <div class="restaurant_discount_options_Tuesday_div restaurant_discount" style="display:none">
                                    <table class="booking-table" id="working_hour_table_Tuesday">
                                        <tr>
                                            <th>
                                                <label class="col-3 control-label">{{ trans('lang.from') }}</label>
                                            </th>
                                            <th>
                                                <label class="col-3 control-label">{{ trans('lang.to') }}</label>
                                            </th>
                                            <th>
                                                <label class="col-3 control-label">{{ trans('lang.actions') }}</label>
                                            </th>
                                        </tr>
                                    </table>
                                </div>
                                <div class="form-group row">
                                    <label class="col-1 control-label">{{ trans('lang.wednesday') }}</label>
                                    <div class="col-12">
                                        <button type="button" class="btn btn-primary" onclick="addMorehour('Wednesday','wednesday', '1')">
                                            {{ trans('lang.add_more') }}
                                        </button>
                                    </div>
                                </div>
                                <div class="restaurant_discount_options_Wednesday_div restaurant_discount" style="display:none">
                                    <table class="booking-table" id="working_hour_table_Wednesday">
                                        <tr>
                                            <th>
                                                <label class="col-3 control-label">{{ trans('lang.from') }}</label>
                                            </th>
                                            <th>
                                                <label class="col-3 control-label">{{ trans('lang.to') }}</label>
                                            </th>
                                            <th>
                                                <label class="col-3 control-label">{{ trans('lang.actions') }}</label>
                                            </th>
                                        </tr>
                                    </table>
                                </div>
                                <div class="form-group row">
                                    <label class="col-1 control-label">{{ trans('lang.thursday') }}</label>
                                    <div class="col-12">
                                        <button type="button" class="btn btn-primary" onclick="addMorehour('Thursday','thursday', '1')">
                                            {{ trans('lang.add_more') }}
                                        </button>
                                    </div>
                                </div>
                                <div class="restaurant_discount_options_Thursday_div restaurant_discount" style="display:none">
                                    <table class="booking-table" id="working_hour_table_Thursday">
                                        <tr>
                                            <th>
                                                <label class="col-3 control-label">{{ trans('lang.from') }}</label>
                                            </th>
                                            <th>
                                                <label class="col-3 control-label">{{ trans('lang.to') }}</label>
                                            </th>
                                            <th>
                                                <label class="col-3 control-label">{{ trans('lang.actions') }}</label>
                                            </th>
                                        </tr>
                                    </table>
                                </div>
                                <div class="form-group row">
                                    <label class="col-1 control-label">{{ trans('lang.friday') }}</label>
                                    <div class="col-12">
                                        <button type="button" class="btn btn-primary" onclick="addMorehour('Friday','friday', '1')">
                                            {{ trans('lang.add_more') }}
                                        </button>
                                    </div>
                                </div>
                                <div class="restaurant_discount_options_Friday_div restaurant_discount" style="display:none">
                                    <table class="booking-table" id="working_hour_table_Friday">
                                        <tr>
                                            <th>
                                                <label class="col-3 control-label">{{ trans('lang.from') }}</label>
                                            </th>
                                            <th>
                                                <label class="col-3 control-label">{{ trans('lang.to') }}</label>
                                            </th>
                                            <th>
                                                <label class="col-3 control-label">{{ trans('lang.actions') }}</label>
                                            </th>
                                        </tr>
                                    </table>
                                </div>
                                <div class="form-group row">
                                    <label class="col-1 control-label">{{ trans('lang.satuarday') }}</label>
                                    <div class="col-12">
                                        <button type="button" class="btn btn-primary" onclick="addMorehour('Satuarday','satuarday','1')">
                                            {{ trans('lang.add_more') }}
                                        </button>
                                    </div>
                                </div>
                                <div class="restaurant_discount_options_Satuarday_div restaurant_discount" style="display:none">
                                    <table class="booking-table" id="working_hour_table_Satuarday">
                                        <tr>
                                            <th>
                                                <label class="col-3 control-label">{{ trans('lang.from') }}</label>
                                            </th>
                                            <th>
                                                <label class="col-3 control-label">{{ trans('lang.to') }}</label>
                                            </th>
                                            <th>
                                                <label class="col-3 control-label">{{ trans('lang.actions') }}</label>
                                            </th>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset style="display: none;" id="services_feature">
                        <legend>{{ trans('lang.services') }}</legend>
                        <div class="form-group row">
                            <div class="form-check width-100">
                                <input type="checkbox" id="Free_Wi_Fi">
                                <label class="col-3 control-label" for="Free_Wi_Fi">{{ trans('lang.wifi') }}</label>
                            </div>
                            <div class="form-check width-100">
                                <input type="checkbox" id="Good_for_Breakfast">
                                <label class="col-3 control-label" for="Good_for_Breakfast">{{ trans('lang.breakfast') }}</label>
                            </div>
                            <div class="form-check width-100">
                                <input type="checkbox" id="Good_for_Dinner">
                                <label class="col-3 control-label" for="Good_for_Dinner">{{ trans('lang.dinner') }}</label>
                            </div>
                            <div class="form-check width-100">
                                <input type="checkbox" id="Good_for_Lunch">
                                <label class="col-3 control-label" for="Good_for_Lunch">{{ trans('lang.lunch') }}</label>
                            </div>
                            <div class="form-check width-100">
                                <input type="checkbox" id="Live_Music">
                                <label class="col-3 control-label" for="Live_Music">{{ trans('lang.live_music') }}</label>
                            </div>
                            <div class="form-check width-100">
                                <input type="checkbox" id="Outdoor_Seating">
                                <label class="col-3 control-label" for="Outdoor_Seating">{{ trans('lang.outdoor_seating') }}</label>
                            </div>
                            <div class="form-check width-100">
                                <input type="checkbox" id="Takes_Reservations">
                                <label class="col-3 control-label" for="Takes_Reservations">{{ trans('lang.reservations') }}</label>
                            </div>
                            <div class="form-check width-100">
                                <input type="checkbox" id="Vegetarian_Friendly">
                                <label class="col-3 control-label" for="Vegetarian_Friendly">{{ trans('lang.vegetarian_friendly') }}</label>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset class="selfDeliveryOption d-none">
                        <legend>{{ trans('lang.self_delivery_setting') }}</legend>
                        <div class="form-group row">
                            <div class="form-group row width-100">
                                <div class="form-check width-100">
                                    <input type="checkbox" id="enable_self_delivery" class="">
                                    <label class="col-3 control-label" for="enable_self_delivery">{{ trans('lang.enable_self_delivery') }}</label>
                                    <div class="form-text text-muted">
                                        {{ trans('lang.enable_self_delivery_help') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset id="delivery_charges_div">
                        <legend>{{ trans('lang.deliveryCharge') }}</legend>
                        <div class="form-group row">
                            <div class="form-group row width-100">
                                <label class="col-4 control-label">{{ trans('lang.delivery_charges_per_km') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control" id="delivery_charges_per_km">
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-4 control-label">{{ trans('lang.minimum_delivery_charges') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control" id="minimum_delivery_charges">
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-4 control-label">{{ trans('lang.minimum_delivery_charges_within_km') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control" id="minimum_delivery_charges_within_km">
                                </div>
                            </div>                                            
                        </div>
                    </fieldset>
                    <fieldset id="packagingChargeDiv" class='d-none'>
                        <legend>{{ trans('lang.packaging_charge') }}</legend>
                        <div class="form-group row width-100 packagingChargeEnable d-none">
                            <label class="col-4 control-label">{{ trans('lang.packaging_charge') }}</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="packagingCharge">
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>{{ trans('lang.bankdetails') }}</legend>
                        <div class="form-group row">
                            <div class="form-group row width-100">
                                <label class="col-4 control-label">{{ trans('lang.bank_name') }}</label>
                                <div class="col-7">
                                    <input type="text" name="bank_name" class="form-control" id="bankName">
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-4 control-label">{{ trans('lang.branch_name') }}</label>
                                <div class="col-7">
                                    <input type="text" name="branch_name" class="form-control" id="branchName">
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-4 control-label">{{ trans('lang.holder_name') }}</label>
                                <div class="col-7">
                                    <input type="text" name="holer_name" class="form-control" id="holderName">
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-4 control-label">{{ trans('lang.account_number') }}</label>
                                <div class="col-7">
                                    <input type="text" name="account_number" class="form-control" id="accountNumber" onkeypress="return chkAlphabets2(event,'error5')">
                                    <div id="error5" class="err"></div>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-4 control-label">{{ trans('lang.other_information') }}</label>
                                <div class="col-7">
                                    <input type="text" name="other_information" class="form-control" id="otherDetails">
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset id="story_upload_div" style="display: none;">
                        <legend>{{trans('lang.story_plural')}}</legend>
                        <div class="form-group row vendor_image">
                            <label class="col-3 control-label">{{trans('lang.choose_humbling_gif_image')}}</label>
                            <div class="">
                                <div id="story_thumbnail"></div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <input type="file" id="file" onChange="handleStoryThumbnailFileSelect(event)">
                                <div id="uploding_story_thumbnail"></div>
                            </div>
                        </div>
                        <div class="form-group row vendor_image">
                            <label class="col-3 control-label">{{trans('lang.select_story_video')}}</label>
                            <div class="col-md-12">
                                <div id="story_vedios" class="row"></div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <input type="file" id="video_file" onChange="handleStoryFileSelect(event)">
                                <div id="uploding_story_video"></div>
                            </div>
                        </div>
                    </fieldset>
