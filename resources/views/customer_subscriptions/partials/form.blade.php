{{--
    Shared by create and edit, so the two never drift apart. The saving script
    differs between them and lives in each view.
--}}
<fieldset>
    <legend>{{ trans('lang.customer_subscription_info') }}</legend>

    <div class="form-group row width-50">
        <label class="col-3 control-label">{{ trans('lang.plan_title') }}<span class="required-field"></span></label>
        <div class="col-7">
            <input type="text" class="form-control plan_title">
            <div class="form-text text-muted">{{ trans('lang.plan_title_help') }}</div>
        </div>
    </div>

    <div class="form-group row width-50">
        <label class="col-3 control-label">{{ trans('lang.plan_price') }}<span class="required-field"></span></label>
        <div class="col-7">
            <input type="number" class="form-control plan_price" min="0">
            <div class="form-text text-muted">{{ trans('lang.plan_price_help') }}</div>
        </div>
    </div>

    <div class="form-group row width-50">
        <label class="col-3 control-label">{{ trans('lang.plan_period') }}</label>
        <div class="col-7">
            <select class="form-control plan_period">
                <option value="30">{{ trans('lang.plan_period_monthly') }}</option>
                <option value="365">{{ trans('lang.plan_period_annual') }}</option>
            </select>
            <div class="form-text text-muted">{{ trans('lang.plan_period_help') }}</div>
        </div>
    </div>

    <div class="form-group row width-100">
        <label class="col-3 control-label">{{ trans('lang.plan_description') }}</label>
        <div class="col-7">
            <textarea rows="6" class="form-control plan_description"></textarea>
            <div class="form-text text-muted">{{ trans('lang.plan_description_help') }}</div>
        </div>
    </div>

    <div class="form-group row width-100">
        <label class="col-3 control-label">{{ trans('lang.plan_image') }}</label>
        <div class="col-7">
            <input type="file" onChange="handlePlanFileSelect(event)">
            <div id="uploading_plan_image"></div>
            <div class="plan_image_preview" style="display:none;">
                <img id="plan_image" src="" width="150px" height="150px">
            </div>
        </div>
    </div>

    <div class="form-group row width-100">
        <div class="form-check">
            <input type="checkbox" class="plan_enabled" id="plan_enabled" checked>
            <label class="col-3 control-label" for="plan_enabled">{{ trans('lang.plan_enabled') }}</label>
        </div>
    </div>
</fieldset>
