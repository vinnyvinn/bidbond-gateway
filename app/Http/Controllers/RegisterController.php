<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use App\Traits\ApiResponser;
use App\Code;
use App\Group;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendMail;
use Carbon\Carbon;
use App\Services\CompanyService;
use App\Mail\ResetPassword;

class RegisterController extends Controller
{
    use ApiResponser;


    public $companyService;


    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function register(Request $request)
    {
        $valid = $request->validate([
            'email' => 'bail|required|unique:users,email',
            'password' => 'bail|required|min:6|confirmed'
        ]);

        $valid['password'] = Hash::make($valid['password']);

        $user = User::create($valid);

        $user->assign("customer");

        $email_code = generateEmailCode();
        $code = new Code();
        $code->email = $user->email;
        $code->code_email = $email_code;
        $code->save();
       //Send Mail
        dispatch(new SendMail($user, $code->code_email));

        return $this->successMessage('An email has been sent to you for account verification', 200);
    }


    public function verifyAccount(Request $request)
    {
        $code = Code::where('code_email', $request->code)->first();

        $code_invalid = $this->isCodeInvalid($code);

        if ($code_invalid) {
            return $this->errorMessage($code_invalid, 400);
        }

        $user = User::where('email', $code->email)->first();

        $user->email_verified_at = Carbon::now();

        $user->save();

        $companies = json_decode($this->companyService->obtainUserCompanies(['email' => $user->email, 'userid' => $user->id]), true);

        if (!$companies) {
            return $this->successMessage('Account verification success!', 200);
        }

        $this->approveCompaniesWhereUserIsDirector($companies, $user);

        return $this->successMessage('Account verification success!', 200);

    }

    /**
     * @param $code
     * @return string|null
     */
    private function isCodeInvalid($code)
    {
        if (!$code) {
            return 'Account verification failed, Please contact support!';
        }

        if ($code->expired()) {
            return 'Activation Code Expired!, Please Contact Support';
        }

        if ($code->limitExceeded()) {
            return 'Activation Code Limit exceeded for the day!, Please Contact Support';
        }

        return null;
    }

    private function approveCompaniesWhereUserIsDirector($companies, $user): void
    {
        foreach ($companies as $company) {

            $current_directors = json_decode($this->companyService->getCompanyDirectors($company['id']), true);

            $director_id = null;

            foreach ($current_directors as $director) {

                if ($user->email == $director['email'] && $user->id_number == $director['id_number']) {

                    $director_id = $director['id'];

                    break;
                }

            }

            if ($user->active == 1) {
                $this->companyService->directorCompanyApproval(['crp' => $company['crp'], 'company_id' => $company['id'], 'director_id' => $director_id]);
            }

        }
    }

    public function resendActivationEmailCode(Request $request)
    {
        $request->validate(['email' => 'bail|required|email|exists:codes,email']);

        $code = Code::where('email', $request->email)->first();

        $email_code = generateEmailCode();

        $user = User::where('email', $request->email)->first();

        $code->code_email = $email_code;
        $code->count = 0;
        $code->email_code_expiry = Carbon::tomorrow();
        $code->save();

        dispatch(new SendMail($user, $email_code));

        return $this->successMessage('A verification email has been resent to the user account', 200);
    }

    public function resendEmail(Request $request)
    {
        $request->validate(['email' => 'bail|required|email']);

        $user = User::where('email', $request->email)->first();

        $code = Code::where('email', $request->email)->first();

        if (!$user) {
            return $this->errorMessage('That email does not exist.', 404);
        }

        if (!$code) {
            $code = new Code();
            $code->email = $user->email;
            $code->code_email = generateEmailCode();
            $code->save();
        }

        dispatch(new SendMail($user, $code->code_email));

        return $this->successMessage('An email has been resent to you for account verification', 200);
    }

    public function SendVerifyUserEmail(Request $request)
    {
        $request->validate(['email' => 'bail|required|email']);

        $user = User::where('email', $request->email)->firstOrFail();

        $code = Code::where('email', $request->email)->first();


        if (!$code) {
            $code = new Code();
            $code->email = $user->email;
            $code->code_email = generateEmailCode();
            $code->save();
        }

        Mail::to($user)->queue(new ResetPassword($user, $code->code_email));

        return $this->successMessage('An email has been resent to you for account verification', 200);
    }

    public function SetNewPassword(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'password' => 'bail|required|min:8|confirmed',
        ]);

        $code = Code::where('code_email', $request->code)->firstOrFail();
        $user = User::where('email', $code->email)->firstOrFail();
        $user->password = bcrypt($request->password);
        $user->save();
        $code->delete();

        return $this->errorMessage(['message' => 'Something went wrong!.', 'error' => true], Response::HTTP_UNAUTHORIZED);
    }

}
