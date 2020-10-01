<?php

namespace App\Http\Controllers;

use App\BidbondPrice;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Bouncer;

class BidBondPricingController extends Controller
{
    use ApiResponser;

    public function index()
    {
        if (!Bouncer::can('list-bidbond-pricing')) {
            abort('You do not have the rights to list bid pricing', 403);
        }

        return response()->json(BidbondPrice::with('group:id,name')->get());
    }

    public function store(Request $request)
    {
        if (!Bouncer::can('create-bidbond-pricing')) {
            abort('You do not have the rights to update bid pricing', 403);
        }

        $data = $request->validate(BidbondPrice::$createRules);

        $bidbondPrice = BidbondPrice::where('group_id', $data['group_id'])
            ->where('upper_bound', $data['upper_bound'])
            ->where('lower_bound', $data['lower_bound']);

        if ($bidbondPrice->exists()) {
            $bidbondPrice->first()->update($data);

            return response()->json($bidbondPrice->first(), 201);
        }

        return response()->json(BidbondPrice::create($data), 201);
    }

    public function show(BidbondPrice $bidbondPrice)
    {
        return response()->json($bidbondPrice, 200);
    }

    public function update(Request $request, $id)
    {
        if (!Bouncer::can('edit-bidbond-pricing')) {
            abort('You do not have the rights to update bid pricing', 403);
        }

        $bidbondPrice = BidbondPrice::findOrFail($id);
        $bidbondPrice->update($request->validate(BidbondPrice::$updateRules));
        return response()->json($bidbondPrice, 200);
    }

    public function destroy($id)
    {
        if (!Bouncer::can('edit-bidbond-pricing')) {
            abort('You do not have the rights to delete bid pricing group', 403);
        }

        BidbondPrice::findOrFail($id)->delete();

        return response()->json(['message' => "Bidbond price deleted successfully"], 200);
    }

}
