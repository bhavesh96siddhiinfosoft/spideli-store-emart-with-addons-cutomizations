{{-- #113: which store the advert is for (an owner may have several stores, in one or several regions). --}}
<div class="form-group row width-100">
    <label class="col-3 control-label">{{ trans('lang.adv_store') }}</label>
    {{-- custom-dropdown: the panel's own fix for its unscoped .select2-container rule (style.css), as on the other forms --}}
    <div class="col-7 custom-dropdown">
        <select id="adv_store" class="form-control"></select>
        <div class="form-text text-muted adv_store_help">{{ trans('lang.adv_store_help') }}</div>
    </div>
</div>
