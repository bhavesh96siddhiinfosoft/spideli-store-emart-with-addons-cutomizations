{{-- Store helpers, shared by the panel layout and the standalone pages.

     The subscription screens - Choose a Plan, Checkout, Payment Success - are
     not part of layouts/app.blade.php. They render their own <html>, because a
     vendor who has not subscribed yet has no sidebar to show. That means they
     do not get anything defined in the layout, and calls to these helpers from
     those pages failed with "not defined" until this partial existed.

     Nothing here touches jQuery or the DOM - it is firebase and plain
     JavaScript - so it is safe to include on any page, before or after the
     scripts that draw the panel. The helpers that DO need jQuery or the header
     (selectStore, the region picker) stay in the layout.

     `$authRole` and `$empVendorId` are not passed to every screen, so both are
     read with a fallback rather than assumed. --}}
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
                const role = "{{ $authRole ?? '' }}";
                const employeeStoreId = "{{ $empVendorId ?? '' }}";

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

                /* No store yet.
                 *
                 * A vendor subscribes BEFORE they create a store - the plans
                 * screen is the first thing they see after signing up - so the
                 * plan they have just paid for is on the account and there is
                 * nowhere else to look. Answering false here sent a vendor who
                 * had just paid straight back to the plans screen.
                 *
                 * Once a store exists its own answer is the only one that
                 * counts, which is what stops one paid store carrying the
                 * others past the paywall. */
                if (!store) {
                    try {
                        const snapshot = await firebase.firestore()
                            .collection('users').doc(ownerUserId).get();
                        const accountPlanId = snapshot.exists ? snapshot.data().subscriptionPlanId : '';

                        return accountPlanId !== undefined && accountPlanId !== null && accountPlanId !== '';
                    } catch (err) {
                        console.error("Could not read the account subscription:", err);
                        return false;
                    }
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


            /* Signs out a user whose record has gone.
             *
             * An admin can delete a vendor. The Laravel session survives that -
             * it lives in this site's own database - so the vendor keeps
             * browsing with a sign-in that no longer has anything behind it, and
             * every screen that reads their record quietly fails. Rather than
             * leaving them on a broken page, they are signed out and returned to
             * the login screen.
             *
             * Only called when a read SUCCEEDED and reported no such record.
             * Never on a failed read: a moment of no network must not throw
             * people out of the panel. */
            function signOutMissingUser() {
                try {
                    if (firebase.auth) {
                        firebase.auth().signOut();
                    }
                } catch (err) {
                    /* Signing out of the panel matters more than of firebase. */
                }

                var form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('logout') }}";

                var token = document.createElement('input');
                token.type = 'hidden';
                token.name = '_token';
                token.value = "{{ csrf_token() }}";

                form.appendChild(token);
                document.body.appendChild(form);
                form.submit();
            }

        </script>
