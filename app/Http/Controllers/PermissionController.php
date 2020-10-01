<?php

namespace App\Http\Controllers;

use App\Commission;
use Bouncer;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    use ApiResponser;

    public function index()
    {
        $permissions = Bouncer::ability()->all()
            ->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name
                ];
            });

        return $this->successResponse($permissions);
    }

    public function attach(Request $request)
    {
        $this->validate($request, [
            'permission' => 'required',
            'role' => 'required',
        ]);

        $user = auth()->user();

        $user_role = $user->roles()->first();

        $role = DB::table('roles')->where('name', $request->role['name'])->first();

        if($role->id == $user_role->id){
            return $this->errorResponse("User cannot update this role's permissions", 403);
        }

        DB::table('permissions')->where('entity_id', $role->id)->delete();

        $permissions = DB::table('abilities')->whereIn('id', $request->permission)->get();

        foreach ($permissions as $key) {
            Bouncer::allow($request->role['name'])->to($key->name);
        }

        if (!$request->commission) {
            return $this->successResponse("Permissions updated successfully", Response::HTTP_CREATED);
        }

        $commission = Commission::role($request->role['id'])->first();

        if ($commission) {

            $commission->update([
                'amount' => $request->commission['amount'],
                'status' => $request->earns_commission
            ]);

        } else {

            Commission::create([
                'role_id' => $request->role['id'],
                'amount' => $request->commission['amount']
            ]);
        }

        return $this->successResponse("Permissions updated successfully", Response::HTTP_CREATED);
    }

    public function getAbilities(Request $request)
    {
        $validator = Validator::make($request->all(), ['role' => 'required']);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'code' => 'input_invalid',
                    'message' => $validator->errors()->all()
                ]
            ], 422);
        }

        $abilities = DB::table('permissions')
            ->join('abilities', 'abilities.id', '=', 'permissions.ability_id')
            ->join('roles', 'roles.id', '=', 'permissions.entity_id')
            ->where('permissions.entity_id', $request->role['id'])->get()
            ->map(function ($ability) {
                return $ability->ability_id;
            });

        $commissions = Commission::role($request->role['id'])->first();

        return $this->successResponse(['permissions' => $abilities, 'commission' => $commissions]);
    }

}
