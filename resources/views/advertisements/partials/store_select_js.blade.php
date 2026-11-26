{{-- #113: the store select of the advert forms. Declarations only; each form calls loadAdvStores(). Needs
     vendorUserId, authRole and empVendorId (set by the form). --}}
<script type="text/javascript">
    var advStoresById = {};

    /* An owner sees all their stores (any region), each named with its region; an employee only their own store.
     * preselectId: the advert's store (edit, copy), else the store the panel is working on.
     * locked: the store cannot change (a paid advert, or an employee). */
    async function loadAdvStores(preselectId, locked) {
        var db = firebase.firestore();
        var stores = [];
        if (authRole === 'employee') {
            if (empVendorId) {
                var own = await db.collection('vendors').doc(empVendorId).get();
                if (own.exists) {
                    stores.push(Object.assign({id: own.id}, own.data()));
                }
            }
        } else {
            var snap = await db.collection('vendors').where('author', '==', vendorUserId).get();
            snap.docs.forEach(function (d) { stores.push(Object.assign({id: d.id}, d.data())); });
        }
        var regionNames = {};
        var regionIds = stores.map(function (s) { return s.regionId; }).filter(function (r, i, a) { return r && a.indexOf(r) === i; });
        await Promise.all(regionIds.map(async function (rid) {
            var r = await db.collection('regions').doc(rid).get();
            regionNames[rid] = r.exists ? (r.data().name || '') : '';
        }));
        stores.sort(function (a, b) { return String(a.title || '').localeCompare(String(b.title || '')); });
        var $s = $('#adv_store').empty();
        advStoresById = {};
        stores.forEach(function (s) {
            advStoresById[s.id] = s;
            var label = (s.title || s.id) + (regionNames[s.regionId] ? ' · ' + regionNames[s.regionId] : '');
            $s.append($('<option></option>').attr('value', s.id).text(label));   // .text(): names are not HTML
        });
        var pick = preselectId && advStoresById[preselectId] ? preselectId : '';
        if (!pick && typeof resolveCurrentStore === 'function') {
            var current = await resolveCurrentStore(vendorUserId);
            pick = current && advStoresById[current.id] ? current.id : '';
        }
        if (!pick && stores.length) {
            pick = stores[0].id;
        }
        $s.val(pick);
        $s.prop('disabled', !!locked || authRole === 'employee' || stores.length < 2);
        if ($.fn.select2) {
            $s.select2({width: '100%'});
        }
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
