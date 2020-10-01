<?php

namespace App\Http\Controllers;

use App\Role;
use App\Traits\ApiResponser;
use App\User;
use Bouncer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    use ApiResponser;

    public function index()
    {
        $roles = Bouncer::role()->select('id', 'name', 'created_at')->get();

        return $this->successResponse($roles);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:roles,name|max:191']);

        return Bouncer::role()->firstOrCreate(['name' => Str::slug(strtolower($request->name), '_')]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'bail|required|numeric|exists:roles,id',
            'name' => 'bail|required|max:191|unique:roles,name',
        ]);

        $role = Role::find($request->id);

        $allowed_roles = collect(['customer', 'agent', 'support', 'relationship_manager', 'operations']);

        if ($allowed_roles->contains($role->name)) {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'message' => ['You cannot edit the system role ' . $role->name]]
            ], 422);
        }

        $role->update(['name' => Str::slug(strtolower($request->name), '_')]);

        return $this->successResponse("Role updated successfully", Response::HTTP_OK);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'bail|required|numeric|unique:roles,id,' . $request->name . ',name',
        ]);

        $role = Role::find($request->id);

        $has_users = User::whereIs($role->name)->exists();

        if ($has_users) {
            return $this->errorResponse("Cannot delete role while users are assigned", 422);
        }

        $role->delete();

        return $this->successResponse("Role deleted successfully", Response::HTTP_OK);
    }

    public function otherRoles()
    {
        $roles = DB::table('roles')
            ->select('id', 'name', 'created_at')
            ->whereNotIn('name', ['superadmin', 'customer'])->get();

        return $this->successResponse($roles);
    }
}
