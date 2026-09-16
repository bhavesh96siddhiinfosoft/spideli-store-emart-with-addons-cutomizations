<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\VendorUsers;

/**
 * The stores belonging to the signed-in vendor.
 *
 * One account may own several stores - they are `vendors` documents sharing an
 * `author`. See docs/app-spec-multiple-stores.md.
 *
 * The store form itself lives in `resources/views/stores/partials/`, shared by
 * create and edit so the two cannot drift.
 */
class StoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('stores.index')->with('id', $this->vendorUserId());
    }

    public function create()
    {
        return view('stores.create')->with('id', $this->vendorUserId());
    }

    public function edit($id)
    {
        return view('stores.edit')
            ->with('storeId', $id)
            ->with('id', $this->vendorUserId());
    }

    public function view($id)
    {
        return view('stores.view')
            ->with('storeId', $id)
            ->with('id', $this->vendorUserId());
    }

    private function vendorUserId()
    {
        $exist = VendorUsers::where('user_id', Auth::id())->first();

        return $exist->uuid;
    }
}
