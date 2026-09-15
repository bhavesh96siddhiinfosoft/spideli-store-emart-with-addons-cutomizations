<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\VendorUsers;

/**
 * Subscriptions a store sells to its own customers.
 *
 * Not to be confused with SubscriptionPlan / my-subscriptions, which are what
 * the store itself buys from the platform. These are three separate systems and
 * three separate collections - see docs/app-spec-vendor-subscription.md.
 */
class CustomerSubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('customer_subscriptions.index')->with('id', $this->vendorUserId());
    }

    public function create()
    {
        return view('customer_subscriptions.create')->with('id', $this->vendorUserId());
    }

    public function edit($id)
    {
        return view('customer_subscriptions.edit')
            ->with('id', $id)
            ->with('vendorUserId', $this->vendorUserId());
    }

    public function subscribers()
    {
        return view('customer_subscriptions.subscribers')->with('id', $this->vendorUserId());
    }

    public function payments()
    {
        return view('customer_subscriptions.payments')->with('id', $this->vendorUserId());
    }

    private function vendorUserId()
    {
        $exist = VendorUsers::where('user_id', Auth::id())->first();

        return $exist->uuid;
    }
}
