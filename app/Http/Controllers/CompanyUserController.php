<?php

namespace App\Http\Controllers;

use App\Services\CompanyService;
use App\User;
use Illuminate\Http\Request;
use Bouncer;

class CompanyUserController extends Controller
{
    public $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function index($company_id)
    {
        $user_array = collect(json_decode($this->companyService->getCompanyUsers($company_id), true));

        $users = User::whereIn('id', $user_array->pluck("user_id"))
            ->whereIs('customer')
            ->get();


        $users = $users->map(function ($user) use ($user_array) {
            $user->status = $user_array->where('user_id', $user->id)->first()['creator'];
            return $user;
        });

        return response()->json($users);
    }

    public function store(Request $request, $company_id)
    {
        $request->validate([
            'email' => 'bail|required|email|exists:users,email'
        ]);

        $this->canAttachUserGuard($company_id);

        $user = User::where('email', $request->email)
            ->whereIs('customer')
            ->firstOrFail();
        $response = json_decode($this->companyService->attachUser([
            'user_id' => $user->id,
            'company_id' => $company_id
        ]), true);

        return response()->json($response, $response["code"]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'email' => 'bail|required|email|exists:users,email',
            'company_id' => 'required'
        ]);

        $this->canAttachUserGuard($request->company_id);

        $user = User::where('email', $request->email)
            ->whereIs('customer')
            ->firstOrFail();

        $response = json_decode($this->companyService->detachUser([
            'user_id' => $user->id,
            'company_id' => $request->company_id
        ]), true);

        return response()->json($response, $response["code"]);
    }

    /**
     * @param $company_id
     */
    private function canAttachUserGuard($company_id): void
    {
        if (auth()->user()->isAn('customer')) {

            $user_array = collect(json_decode($this->companyService->getCompanyUsers($company_id), true));

            $is_company_user = $user_array->where('user_id', auth()->id())->where('status', 1)->first();

            if (!$is_company_user) {
                abort(403, 'User does not have permissions to link user to the company');
            }
        }

        if (!Bouncer::can('attach-company-users') && !auth()->user()->isAn('customer')) {
            abort(403, 'User does not have attach company permissions');
        }
    }

}
