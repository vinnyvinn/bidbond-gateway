<?php

namespace App\Http\Controllers;

use App\RowCommision;
use Illuminate\Support\Facades\Auth;
use Bouncer;

class CommissionController extends Controller
{
    public function index()
    {
        if (Bouncer::can('list-commission-owned')) {
            return response()->json(RowCommision::ofUser(Auth::guard('api')->user()->id)->latest()->paginate());
        }

        if (Bouncer::can('list-commission')) {
            return response()->json(RowCommision::with('user')->latest()->paginate());
        }

        abort('403', 'You do not have permissions to list commissions');
    }


}
