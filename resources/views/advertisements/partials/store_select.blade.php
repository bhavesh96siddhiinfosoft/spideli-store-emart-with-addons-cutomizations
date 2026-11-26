{{-- #113: which store the advert is for (an owner may have several stores, in one or several regions).
     A Chosen dropdown, like the admin's Delivery Zones (region form). The theme only styles Chosen's multi-select,
     so the single one is fitted to the form fields here, scoped to this row. --}}
<style>
    /* the same box as the other fields of the form (.form-control: 38px, #ccc border, 16px) */
    .adv-store-box .chosen-container { width: 100% !important; font-size: 16px; }
    .adv-store-box .chosen-container-single .chosen-single {
        height: 38px; line-height: 36px; padding: 0 0 0 12px; border: 1px solid #ccc; border-radius: 4px;
        background: #f5f5f5; box-shadow: none; color: #67757c;
    }
    .adv-store-box .chosen-container-single .chosen-default { color: #999; }
    /* 18px, Chosen's own width: its arrow is a sprite (down arrow, then the up arrow 18px further), so a wider
       box showed part of the second arrow at the right edge */
    .adv-store-box .chosen-container-single .chosen-single div { width: 18px; right: 6px; }
    .adv-store-box .chosen-container-single .chosen-single div b { background-position: 0 9px; }
    .adv-store-box .chosen-container-active.chosen-with-drop .chosen-single div b { background-position: -18px 9px; }
    .adv-store-box .chosen-container-active.chosen-with-drop .chosen-single {
        background: #fff; border-color: #5897fb; border-bottom-left-radius: 0; border-bottom-right-radius: 0;
    }
    .adv-store-box .chosen-container .chosen-drop { border-color: #5897fb; }
    .adv-store-box .chosen-container-single .chosen-search input[type="text"] { height: 34px; padding: 4px 26px 4px 8px; }
    .adv-store-box .chosen-container .chosen-results { margin: 0; padding: 0; max-height: 240px; }
    .adv-store-box .chosen-container .chosen-results li { padding: 7px 10px; line-height: 20px; }
    .adv-store-box .chosen-container .chosen-results li.no-results { background: #fff; color: #777; }
    .adv-store-box .chosen-container-single.chosen-disabled .chosen-single { cursor: default; }
</style>
<div class="form-group row width-100">
    <label class="col-3 control-label">{{ trans('lang.adv_store') }}</label>
    <div class="col-7 adv-store-box">
        <select id="adv_store" class="form-control"></select>
        <div class="form-text text-muted adv_store_help">{{ trans('lang.adv_store_help') }}</div>
    </div>
</div>
