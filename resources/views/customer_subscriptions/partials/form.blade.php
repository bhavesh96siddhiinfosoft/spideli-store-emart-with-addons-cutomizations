{{--
    Shared by create and edit, so the two never drift apart. The saving script
    differs between them and lives in each view.
--}}
<fieldset>
    <legend>{{ trans('lang.customer_subscription_info') }}</legend>

    <div class="form-group row width-50">
        <label class="col-3 control-label required-field">{{ trans('lang.plan_store') }}</label>
        <div class="col-7 custom-dropdown">
            <select class="form-control" id="plan_store"></select>
            <div class="form-text text-muted">{{ trans('lang.plan_store_help') }}</div>
        </div>
    </div>

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

<fieldset>
    <legend>{{ trans('lang.plan_points') }}</legend>

    <div class="form-group row width-100">
        <div class="col-12">
            <div id="plan_points_container"></div>
            <button type="button" class="btn btn-primary mt-2" onclick="addPlanPoint()">{{ trans('lang.add_more') }}</button>
            <div class="form-text text-muted">{{ trans('lang.plan_points_help') }}</div>
        </div>
    </div>
</fieldset>

{{--
    Shared by create and edit, like the markup above. Only a function is declared
    here - nothing runs at this point, because jQuery and select2 are loaded
    further down the page. Each view calls it from its own ready handler.
--}}
<script type="text/javascript">

    /* The stores this plan may belong to, by id, so the saving script can read
     * the chosen store's region and section without another query. */
    var planStoresById = {};

    /* Offers the account's stores IN THE REGION THE PANEL IS WORKING IN - the
     * one named in the header - because a plan belongs to one store and a vendor
     * may hold several in the same region.
     *
     * Stores in the vendor's other regions are deliberately not offered: a
     * region is chosen in the header, and a plan for a store elsewhere would be
     * invisible the moment the vendor switched back. */
    async function loadPlanStores(ownerUserId, storeIdToSelect) {
        var $select = $('#plan_store');

        if (!$select.length) {
            return;
        }

        try {
            var current = await resolveCurrentStore(ownerUserId);
            var currentRegionId = current ? (current.regionId || '') : '';

            var snapshot = await firebase.firestore().collection('vendors')
                .where('author', '==', ownerUserId).get();

            var stores = [];

            snapshot.docs.forEach(function (doc) {
                var store = doc.data();
                store.id = store.id || doc.id;

                /* The plan's own store is always offered, even when the header
                 * is on another region - otherwise editing a plan from a region
                 * you are not currently in would silently offer to move it. */
                var isOwnStore = storeIdToSelect && store.id === storeIdToSelect;

                if (!isOwnStore && currentRegionId !== '' && (store.regionId || '') !== currentRegionId) {
                    return;
                }

                planStoresById[store.id] = store;
                stores.push(store);
            });

            stores.sort(function (a, b) {
                return (a.title || '').localeCompare(b.title || '');
            });

            $select.empty().append($("<option></option>")
                .attr("value", "")
                .text("{{ trans('lang.select_store_filter') }}"));

            stores.forEach(function (store) {
                $select.append($("<option></option>")
                    .attr("value", store.id)
                    .text(store.title || store.id));
            });

            /* Editing keeps the plan's own store. Creating opens on the store
             * the panel is working on, which is the one a vendor expects. */
            var preselect = storeIdToSelect || (current ? current.id : '');

            if (preselect && planStoresById[preselect]) {
                $select.val(preselect);
            }

            $select.select2({
                placeholder: "{{ trans('lang.select_store_filter') }}",
                width: '100%'
            });
        } catch (err) {
            console.error("Could not load the stores for this plan:", err);
        }
    }


    /* The plan's selling points, shown to a customer on the plan.
     *
     * Same shape and field name as the platform's own plans in the admin panel -
     * `plan_points`, an array of strings - so the two read alike wherever they
     * are displayed side by side.
     *
     * The array is the truth and the inputs are redrawn from it, rather than
     * reading the boxes back at save time. Deleting the middle of three would
     * otherwise leave the remaining inputs carrying stale positions. */
    var planPoints = [];

    function renderPlanPoints() {
        var container = document.getElementById('plan_points_container');

        if (!container) {
            return;
        }

        container.innerHTML = '';

        planPoints.forEach(function (point, index) {
            var row = document.createElement('div');
            row.className = 'form-group d-flex option-row mt-1';

            var input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control';
            /* Set as a property, not as markup - a point containing a quote
             * would otherwise break out of the attribute. */
            input.value = point;
            input.addEventListener('input', function () {
                planPoints[index] = this.value;
            });

            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-danger ml-2';
            remove.innerHTML = '<i class="mdi mdi-delete"></i>';
            remove.addEventListener('click', function () {
                planPoints.splice(index, 1);
                renderPlanPoints();
            });

            row.appendChild(input);
            row.appendChild(remove);
            container.appendChild(row);
        });
    }

    function addPlanPoint() {
        planPoints.push('');
        renderPlanPoints();
    }

    /* Points are optional - a plan may have none. Empty boxes are not saved as
     * blank lines: they are dropped, so a vendor who adds a row and changes
     * their mind is not stopped from saving. */
    function cleanedPlanPoints() {
        return planPoints
            .map(function (point) { return (point || '').trim(); })
            .filter(function (point) { return point !== ''; });
    }

    function setPlanPoints(points) {
        planPoints = Array.isArray(points) ? points.slice() : [];
        renderPlanPoints();
    }

</script>
