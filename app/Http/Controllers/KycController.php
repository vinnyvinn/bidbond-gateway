<?php

namespace App\Http\Controllers;

use App\KycStatus;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KycController extends Controller
{
    use ApiResponser;

    public function index()
    {
        return $this->successResponse(KycStatus::with('role')->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'bail|required|numeric',
            'status' => 'bail|required|numeric']);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'code' => 'input_invalid',
                    'message' => $validator->errors()->all()
                ]
            ], 422);
        }

        $data = KycStatus::firstOrNew(['role_id' => $request->role_id, 'status' => $request->status])->save();

        return response()->json([$data], 201);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'bail|required|numeric',
            'status' => 'bail|required|numeric']);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'code' => 'input_invalid',
                    'message' => $validator->errors()->all()
                ]
            ], 422);
        }

        $data = KycStatus::where(['role_id' => $request->role_id])->update([
            'status' => $request->status
        ]);

        return response()->json([$data], 200);
    }

    public function destroy(request $request)
    {
        $validator = Validator::make($request->all(), ['role_id' => 'required|numeric']);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'code' => 'input_invalid',
                    'message' => $validator->errors()->all()
                ]
            ], 422);
        }

        KycStatus::where('role_id', $request->role_id)->delete();

        return $this->successResponse('Kyc status deleted!', 200);
    }

}
