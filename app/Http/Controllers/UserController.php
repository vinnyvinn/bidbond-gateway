<?php

namespace App\Http\Controllers;

use App\Rules\MatchEmailDomain;
use App\Traits\Searches;
use Illuminate\Http\Request;
use App\User;
use App\Traits\ApiResponser;
use Bouncer;
use App\Services\CompanyService;
use App\Jobs\NotifyUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use ApiResponser, Searches;


    public $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function index()
    {
        $users = User::select('users.firstname', 'users.lastname', 'users.middlename', 'users.email', 'users.phone_number', 'users.id_number',
            'users.user_unique_id', 'users.created_at', 'users.active', 'roles.name as role')
            ->join('assigned_roles', 'users.id', 'assigned_roles.entity_id')
            ->join('roles', 'roles.id', 'assigned_roles.role_id')
            ->whereNotIn('roles.name', ['superadmin', 'customer', 'agent'])->paginate();

        return $this->successResponse($users);
    }

    public function listDeleted()
    {
        return $this->successResponse(User::onlyTrashed()->with('roles')->paginate());
    }

    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();
        return $this->successResponse($user);
    }

    public function getCustomers()
    {
        $customers = User::select('firstname', 'lastname', 'middlename', 'email', 'phone_number', 'id_number',
            'user_unique_id', 'created_at', 'active')
            ->whereIs('customer')
            ->search(\request('search'))
            ->paginate();

        return $this->successResponse($customers);
    }

    public function store(Request $request)
    {
        $request->validate([
            'firstname' => 'bail|required|max:191',
            'lastname' => 'bail|required|max:191',
            'middlename' => 'nullable|max:191',
            'phone_number' => 'bail|required|max:15',
            'id_number' => 'bail|required|max:20|unique:users,id_number',
            'role' => 'required|max:191|exists:roles,name',
            'email' => ['bail', 'max:191', 'required', 'email', 'unique:users,email', new MatchEmailDomain]
        ]);

        $data = $request->only(['firstname', 'lastname', 'middlename', 'phone_number',
            'id_number', 'email'
        ]);

        $pass = getToken(6);

        $loggedInuser = auth()->user();

        $data = array_merge($data, [
            'password' => Hash::make($pass),
            'verified_otp' => 1,
            'verified_phone' => 1,
            'create_by_admin' => 1,
            'active' => 1,
            'requires_password_change' => 1,
            'email_verified_at' => now()
        ]);

        $user = User::create($data);

        $user->assign(strtolower($request->role));

        NotifyUser::dispatch($user, $loggedInuser->firstname . " " . $loggedInuser->lastname, $pass);

        return $this->successMessage("An account has been created and an email sent to $request->email", 201);
    }

    public function show($id)
    {
        $user = User::whereNotIn('email', ['superadmin@hfgroup.com'])
            ->where('user_unique_id', $id)
            ->firstorfail();

        if ($user) {
            $user['role'] = $user->getRoles()->first();
            $user['permissions'] = $user->getAbilities();
        }

        return $this->successResponse($user, 200);
    }

    public function changeRole(Request $request)
    {
        $request->validate([
            'role' => 'bail|required|max:191|exists:roles,name',
            'userid' => 'bail|required|max:191']);

        $user = User::where('user_unique_id', $request->userid)->first();

        if ($user->id == auth()->id()) {
            return response()->json([
                'status' => 'error',
                'error' => ['message' => 'You cannot change this user role']
            ], 422);
        }

        DB::table('assigned_roles')->where('entity_id', $user->id)->delete();

        $user->assign(strtolower($request->role));

        return 'Updated';
    }

    public function suspend(Request $request)
    {
        $request->validate(['userid' => 'required|max:191|exists:users,user_unique_id']);

        User::where('user_unique_id', $request->userid)->update(['active' => 0]);

        return 'User suspended';
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => 'bail|required|max:191|exists:users,user_unique_id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'message' => $validator->errors()->all()
                ]
            ], 422);
        }

        User::where('user_unique_id', $request->userid)->delete();

        return 'User deleted';
    }

    public function activate(Request $request)
    {
        $request->validate([
            'userid' => 'required|max:191'
        ]);

        User::where('user_unique_id', $request->userid)->update(['active' => 1]);

        return 'User activated';
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'firstname' => 'bail|required|max:191',
            'middlename' => 'max:191',
            'lastname' => 'bail|required|max:191',
            'phone_number' => 'bail|required|max:15',
            'id_number' => 'bail|required|max:20|unique:users,id_number,' . $request->id,
            'email' => ['bail', 'max:191', 'required', 'email', 'unique:users,email,' . $request->id],
            'role' => 'required',
        ]);

        $allowed_roles = collect(['customer', 'agent']);

        if (!$allowed_roles->contains($request->role)) {
            $validator = Validator::make($request->all(), [
                'email' => new MatchEmailDomain
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'error' => ['message' => $validator->errors()->all()]
                ], 422);
            }
        }

        $user = User::where('user_unique_id', $id)->first();
        $user_email = $user->email;
        DB::table('assigned_roles')->where('entity_id', $user->id)->delete();
        $user->assign(strtolower($request->role));
        $data = $request->only(['firstname', 'middlename', 'lastname', 'phone_number', 'id_number', 'email']);

        if ($user->email == $request->email) {
            $user->fill($data);
        }

        $auth_user = auth()->user();
        $pass = getToken(6);
        $data['password'] = Hash::make($pass);
        $user->fill($data);
        $user->save();

        NotifyUser::dispatch($user, $auth_user->firstname . " " . $auth_user->lastname, $pass);

        $data['user_email'] = $user_email;

        if (strtolower($request->role) == 'customer') {
            $this->companyService->updateDirector($data);
        }

        return $this->successResponse($user);
    }

//    used by company service
    public function userSearch($phone, $id_number)
    {
        if (config('services.informa.phone_search_active')) {
            $valid = $this->searchByPhoneNId($phone, $id_number);
            return ['status' => $valid];
        }
        return ['status' => false];
    }

    public function relationship_managers()
    {
        return response()->json(
            User::whereIs('relationship_manager')->select('id', 'firstname', 'middlename', 'lastname')->get()
        );
    }
}
