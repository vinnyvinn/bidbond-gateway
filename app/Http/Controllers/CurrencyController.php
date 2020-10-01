<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function getConversionRate(Request $request)
    {
        $data = $this->validate($request,[
            'from' => 'required',
            'to' => 'required'
        ]);

        //#TODO:
        // branch specific
        // $rate = $bankingService->getConversionRate($data);
        // return response->json(["rate" => $rate]);
    }
}
