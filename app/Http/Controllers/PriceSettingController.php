<?php

namespace App\Http\Controllers;

use App\PriceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PriceSettingController extends Controller
{

    public function index()
    {
        return response()->json(PriceSetting::all());
    }

    public function update(Request $request, PriceSetting $priceSetting)
    {
        $data = $request->validate([
            'option' => 'unique:price_settings,option,' . $priceSetting->id,
            'value' => 'required'
        ]);

        $priceSetting->update($data);

        $this->updateCacheSetting($request);

        return response()->json($priceSetting->refresh(), 200);
    }


    private function updateCacheSetting(Request $request): void
    {
        if(Cache::has($request->option)){
            Cache::forget($request->option);
            Cache::rememberForever($request->option, function () use ($request) {
                return $request->value;
            });
        }
    }

    public function searchCost()
    {
        return Cache::rememberForever(PriceSetting::cr12_search_cost, function () {
            return PriceSetting::option(PriceSetting::cr12_search_cost)->first()->value;
        });
    }
}
