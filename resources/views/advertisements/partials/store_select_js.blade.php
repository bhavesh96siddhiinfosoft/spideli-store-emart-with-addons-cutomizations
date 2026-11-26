{{-- #113: the store select of the advert forms. Declarations only; each form calls loadAdvStores(). Needs
     vendorUserId, authRole and empVendorId (set by the form). --}}
<script type="text/javascript">
    var advStoresById = {};

    /* An owner sees all their stores (any region), each named with its region; an employee only their own store.
     * preselectId: the advert's store (edit, copy), else the store the panel is working on.
     * locked: the store cannot change (a paid advert, or an employee).
     * Every read is guarded: whatever fails, the store the panel is working on is still offered. */
    async function loadAdvStores(preselectId, locked) {
        var db = firebase.firestore();
        var byId = {};
        function add(id, data) {
            if (id && !byId[id]) {
                byId[id] = Object.assign({}, data || {}, {id: id});
            }
        }
        try {
            if (authRole === 'employee') {
                if (empVendorId) {
                    var own = await db.collection('vendors').doc(empVendorId).get();
                    if (own.exists) {
                        add(own.id, own.data());
                    }
                }
            } else if (vendorUserId) {
                var snap = await db.collection('vendors').where('author', '==', vendorUserId).get();
                snap.docs.forEach(function (d) { add(d.id, d.data()); });
            }
        } catch (e) {
            console.error('[adv store] stores could not be read', e);
        }
        var current = null;
        try {
            if (typeof resolveCurrentStore === 'function') {
                current = await resolveCurrentStore(vendorUserId);
            }
        } catch (e) {
            console.error('[adv store] current store could not be read', e);
        }
        if (current && (current.id || '') !== '') {
            add(current.id, current);   // always offered, even if the list above failed
        }
        if (preselectId && !byId[preselectId] && authRole !== 'employee') {
            try {
                var pre = await db.collection('vendors').doc(preselectId).get();
                if (pre.exists && (pre.data().author || '') === vendorUserId) {
                    add(pre.id, pre.data());
                }
            } catch (e) {
                console.error('[adv store] advert store could not be read', e);
            }
        }
        var stores = Object.keys(byId).map(function (k) { return byId[k]; });
        var regionNames = {};
        var regionIds = stores.map(function (s) { return s.regionId; }).filter(function (r, i, a) { return r && typeof r === 'string' && a.indexOf(r) === i; });
        await Promise.all(regionIds.map(async function (rid) {
            try {
                var r = await db.collection('regions').doc(rid).get();
                regionNames[rid] = r.exists ? (r.data().name || '') : '';
            } catch (e) {
                regionNames[rid] = '';
            }
        }));
        stores.sort(function (a, b) { return String(a.title || '').localeCompare(String(b.title || '')); });
        var $s = $('#adv_store');
        if ($.fn.select2 && $s.hasClass('select2-hidden-accessible')) {
            $s.select2('destroy');
        }
        $s.empty();
        advStoresById = {};
        stores.forEach(function (s) {
            advStoresById[s.id] = s;
            var label = (s.title || s.id) + (regionNames[s.regionId] ? ' · ' + regionNames[s.regionId] : '');
            $s.append($('<option></option>').attr('value', s.id).text(label));   // .text(): names are not HTML
        });
        var pick = preselectId && advStoresById[preselectId] ? preselectId : '';
        if (!pick && current && advStoresById[current.id]) {
            pick = current.id;
        }
        if (!pick && stores.length) {
            pick = stores[0].id;
        }
        $s.val(pick);
        $s.prop('disabled', !!locked || authRole === 'employee' || stores.length < 2);
        if ($.fn.select2) {
            $s.select2({width: '100%'});
        }
        $s.trigger('change.select2');
        $s.off('change.advstore').on('change.advstore', function () { spideliAdvStoreRating(advStoresById[$(this).val()]); });
        spideliAdvStoreRating(advStoresById[pick]);
        return pick;
    }

    function selectedAdvStoreId() {
        var v = $('#adv_store').val();
        return v && advStoresById[v] ? v : '';
    }

    // The preview shows the chosen store's rating and number of reviews.
    function spideliAdvStoreRating(store) {
        if (!store) {
            return;
        }
        var count = parseFloat(store.reviewsCount) || 0, sum = parseFloat(store.reviewsSum) || 0;
        var rating = count > 0 && sum > 0 ? Math.round((sum / count) * 10) / 10 : 0;
        $('#rating_data').html(rating);
        $('#preview-review').html(count === 0 ? '(0)' : '(+' + count + ')');
    }
</script>
