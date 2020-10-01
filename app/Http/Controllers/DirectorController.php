<?php

namespace App\Http\Controllers;

use App\Traits\KycCheck;
use App\Traits\Searches;
use function GuzzleHttp\json_decode;
use Illuminate\Http\Response;
use App\Services\CompanyService;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Code;
use App\Jobs\NotifyDirector;
use Illuminate\Http\Request;
use App\Jobs\SendSMS;
use App\Services\BidBondService;
use Carbon\Carbon;
use App\Mail\CompanyActivate;
use App\CompanyCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DirectorController extends Controller
{
    use Searches, ApiResponser, KycCheck;

    public $companyService;
    public $bidBondService;

    public function __construct(CompanyService $companyService, BidBondService $bidBondService)
    {
        $this->companyService = $companyService;
        $this->bidBondService = $bidBondService;
    }


    public function createDirector(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required',
            'phone_number' => 'bail|required|digits:12',
            'id_number' => 'required|numeric',
            'email' => 'bail|required|email|max:191',
            'citizenship' => 'bail|required|in:kenyan,foreigner',
            'firstname' => 'bail|required|max:191',
            'middlename' => 'max:191',
            'lastname' => 'bail|required|max:191',
        ]);

        $user = Auth::guard('api')->user();

        $resp = $this->companyService->createDirector([
            'id_number' => $request->id_number,
            'phone_number' => $request->phone_number,
            'user_phone' => $user->phone_number,
            'email' => $request->email,
            'companyid' => $request->company_id,
            'user_name' => $user->firstname,
            'first_name' => $request->firstname,
            'last_name' => $request->lastname,
            'middle_name' => $request->middlename ?? '',
            'kyc_status' => $this->kycEnabled()
        ]);
       $data = json_decode($resp, true);

        return response()->json($data, $data["code"]);
    }


    public function createDirectorManual(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required',
            'phone_number' => 'bail|required|digits:12',
            'id_number' => 'required|numeric',
            'email' => 'bail|required|email|max:191',
            'citizenship' => 'bail|required|in:kenyan,foreigner',
            'firstname' => 'bail|required|max:191',
            'middlename' => 'nullable|max:191',
            'lastname' => 'bail|required|max:191',
        ]);


        if (config('services.informa.phone_search_active')) {
            $valid = $this->searchByPhoneNId($request->phone_number, $request->id_number);

            if (!$valid) {
                return $this->errorMessage(['error' => ['Your Identification document does not match the phone number provided'], 'code' => 422], 422);
            }
        }

        $data = $request->only([
            'company_id', 'phone_number',
            'id_number', 'email',
            'citizenship', 'firstname',
            'middlename', 'lastname',
        ]);

        $data['user_phone'] = Auth::guard('api')->user()->phone_number;
        $data['user_name'] = Auth::guard('api')->user()->firstname;

        return $this->companyService->manualCreate($data);
    }


    function createDirectorUserAccount($data)
    {
        $pass = strtoupper(Str::random(6));

        $user = User::create([
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'middlename' => $data['middlename'],
            'requires_password_change' => 1,
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'id_number' => $data['id_number'],
            'password' => Hash::make($pass),
            'verified_phone' => 1,
            'verified_otp' => 1,
        ]);

        $user->refresh();

        $user->assign("customer");

        $code = Code::create([
            "email" => $user->email,
            "code_email" => generateEmailCode(),
            "phone_number" => $user->phone_number
        ]);

        $code->refresh();

        $loggedInuser = Auth::guard('api')->user();

        $creator = $loggedInuser->firstname . " " . $loggedInuser->lastname;

        NotifyDirector::dispatchNow($user, $creator, $pass);

        $this->dispatch(
            new SendSMS($data['phone_number'], 'You have been created as a director on ' . config("app.name") . ' by ' . $creator . ' using your email ' . $data['email'] . ' click ' . env('EMAIL_VERIFICATION_URL') . $code->code_email . ' to activate your account and login with password ' . $pass)
        );

        return $this->successMessage('Director created', 200);
    }


    public function activateDirector(Request $request): void
    {
        $this->validate($request, [
            'email' => 'bail|required|email|max:191',
            'code' => 'required',
            'password' => 'bail|required|min:8|confirmed',
        ]);

        $code = Code::where('code_email', $request->code)->firstOrFail();

        $user = User::where('email', $code->email)->firstOrFail()->update([
            "email_verified_at" => Carbon::now(),
            "password" => Hash::make($request->password),
            "email" => $request->email,
            "active" => 1,
            "verified_email" => 1,
        ]);

        $this->companyService->updateDirectorDetails(['id_number' => $user->id_number, 'email' => $user->email]);

        $companies = json_decode($this->companyService->obtainUserCompanies(['email' => $user->email, 'userid' => $user->id]), true);

        foreach ($companies as $company) {

            $current_directors = json_decode($this->companyService->getCompanyDirectors($company['id']), true);

            $user_status = '';

            foreach ($current_directors as $key) {

                $user = User::where('email', $key['email'])->first();

                $user->active ? $user_status = 'active' : $user_status = 'inactive';
            }

            if ($user_status == 'active') {
                $this->companyService->checkApprovalStatus(['crp' => $company['crp'], 'company_id' => $company['id']]);
            }
        }
    }


    public function verifyDirectorCode(Request $request)
    {
        $code = Code::where('code_email', $request->code)->firstOrFail();

        if ($code->email_code_expiry > now() && $code->count < 3) {
            return $this->successMessage('The code is valid', 200);
        }

        return $this->errorMessage('Activation Code Expired!, Please Contact Support', 201);
    }

    public function verifyDirectorSms(Request $request)
    {

        $data = json_decode($this->companyService->VerifyDirectorSms($request->all()),true);
           if ($data['data']['error']) {
            return $this->errorResponse('No Director found!', Response::HTTP_UNAUTHORIZED);
        }

        if ($data['data']['company']['approval_status'] == 'approved') {
            return $this->successMessage('Already Approved', 200);
        }

        $code = generateEmailCode();

        $email = $data['data']['data']['email'];

        $company = $data['data']['company'];

        $company_code = CompanyCode::where('company_id', $company['id'])->first();

        if (!$company_code) {
            info('mhhhh');
            CompanyCode::create([
                "email" => $email,
                "company_id" => $company['id'],
                "code" => $code,
            ]);
        }

        Mail::to($email)->queue(new CompanyActivate($data['data']['data'], $code));

        return $this->successMessage('Director approved', 200);
    }

}
