<?php

namespace App\Http\Controllers;

use App\Services\BidBondService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public $bidbondService;

    public function __construct(BidBondService $bidbondService)
    {
        $this->bidbondService = $bidbondService;
    }

    public function index()
    {
        return response()->json(json_decode($this->bidbondService->obtainSettings(), true));
    }


    public function update(Request $request)
    {
        return response()->json(json_decode($this->bidbondService->updateSettings($request->all()), true));
    }

}
