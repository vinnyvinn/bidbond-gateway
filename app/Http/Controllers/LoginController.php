<?php

namespace App\Http\Controllers;

use App\RowCommision;
use App\Traits\Searches;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Exception;
use function GuzzleHttp\json_decode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Code;
use App\Jobs\SendSMS;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Role;
use App\Services\CompanyService;
use App\Services\BidBondService;
use App\Services\PaymentService;
use App\Services\CounterPartyService;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public $bidBondService, $company_service, $counter_party_service, $paymentService;

    use Searches;

    use ApiResponser;

    public function __construct(BidBondService $bidBondService, CompanyService $company_service, CounterPartyService $counter_party_service, PaymentService $paymentService)
    {
        $this->bidBondService = $bidBondService;
        $this->company_service = $company_service;
        $this->counter_party_service = $counter_party_service;
        $this->paymentService = $paymentService;
    }

    public function login(Request $request)
    {
        $this->validate($request, ['email' => 'bail|required|email',
            'password' => 'bail|required|min:6',]);

        $user = User::where('email', $request->email)->first();

        if ($user) {

            if (!$user->active) {
                return $this->errorMessage("Account has been disabled.Contact System admin", 403);
            }

            if (Hash::check($request->password, $user->password)) {

                $token = $user->createToken('auth_token')->accessToken;

                $usr = $this->initialize($user);

                $usr['access_token'] = $token;

                return response()->json($usr);

            }
        }

        return response("Username or password incorrect", 422);

    }


    public function authLogin(Request $request)
    {
        $validator = Validator::make($request->all(), ['access_token' => 'required']);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'code' => 'input_invalid',
                    'message' => $validator->errors()->all()
                ]
            ], 422);
        }

        $providerUser = null;

        try {
            $providerUser = Socialite::driver("google")->userFromToken($request->access_token);
        } catch (Exception $exception) {

        }

        if (!$providerUser) {
            return $this->errorMessage("Invalid google login attempt", 401);
        }

        $user = User::where('email', $providerUser->getEmail())->first();

        // if user already found
        if (!$user) {

            $user = User::create([
                'email' => $providerUser->getEmail(),
                'password' => Hash::make(getToken()),
                'signup_platform' => "google",
                'email_verified_at' => now(),
                'requires_password_change' => 0
            ]);

        } else {

            if (!$user->active) {
                return $this->errorMessage("Account has been disabled.Contact System admin", 401);
            }

            if (!$user->email_verified_at) {

                $user->update([
                    'email_verified_at' => now(),
                    'requires_password_change' => 0,
                    'signup_platform' => "google"
                ]);
            }
        }

        $token = $user->createToken('auth_token')->accessToken;

        info('user----');
        info($user);
        $usr = $this->initialize($user);

        $usr['access_token'] = $token;

        return response()->json($usr);
    }

    public function initialize($usr = null)
    {
        $usr = $usr ?? auth()->user();

        if (!$usr) {
            return $this->errorMessage('User not logged in', 401);
        }
        $role = $usr->roles()->first();

        $user = $usr->only(['email','id', 'id_number', 'phone_number', 'firstname', 'middlename', 'lastname',
            'verified_otp', 'verified_phone', 'requires_password_change','create_by_admin']);

        $user['role'] = $role ? $role->name : NULL;

        $balance['balance'] = 0;

        if ($role) {
            $kyc_status = $role->kyc_status()->first();
            $kyc_status = $kyc_status ? $kyc_status->status : 1;
            $user['permissions'] = $usr->getAbilities()->pluck('name');
            $user['commision'] = RowCommision::ofUser($usr->id)
                ->whereMonth('created_at', Carbon::today()->month)
                ->sum('commission_amount');
        } else {
            $kyc_status = 1;
        }

        $user['kyc_status'] = (int)$kyc_status;
        $user['balance'] = $balance['balance'];

        return $user;
    }


    public function userOTP(Request $request)
    {
        $validator = Validator::make($request->all(), ['phone_number' => 'required|numeric:12|unique:users,phone_number']);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'error' => ['message' => $validator->errors()->all()]
            ], 422);
        }
        $phone = $request->phone_number;

        $user = Auth::guard('api')->user();

        // Begin Validate phone number
        if (config('services.informa.phone_search_active')) {
            $valid = $this->searchByPhoneNId($phone, $user->id_number);
            if (!$valid) {
                return response()->json([
                    'status' => 'error',
                    'error' => ['message' => 'Your Identification document does not match the phone number provided']
                ], 422);
            }
        }
        // End validate phone number

        $code = Code::where('email', $user->email)->first();

        if (!$code) {
            $user->assign("customer");

            $code = new Code();
            $code->email = $user->email;
            $code->save();
        }

        $smscode = mt_rand(1000, 9999);

        if (!$code) {
            return response()->json([
                'status' => 'error',
                'error' => ['message' => 'Missing code.Please contact administrator']
            ], 422);

        } else {
            if (!$code->code_phone) {

                dispatch(
                    new SendSMS($phone, 'Enter the verification code: ' . $smscode . ' to proceed with registering your ' . config("app.name") . ' Account')
                );
                $code->count = 1;
                $code->code_phone = $smscode;
                $code->phone_number = $phone;
                $code->save();
            } else {

                $code->code_phone = $smscode;
                $code->phone_number = $phone;
                $code->save();

                if (!$code->updated_at->isToday()) { //reset count
                    $code->count = 0;
                    $code->save();
                }
                $codect = $code->count;
                //max allowed sms 3 per phonenumber
                if ($codect > 2) {
                    return $this->errorResponse('You have exceeded the sms validation codes allowed.Please contact the system admin', 400);

                } else {

                    $this->dispatch(
                        new SendSMS($code->phone_number, 'Enter the verification code: ' . $smscode . ' to proceed with registering your ' . config("app.name") . ' Account')
                    );

                    $code->code_phone = $smscode;
                    $code->count = $codect + 1;
                    $code->save();
                }
            }
        }

        return $this->successMessage('An sms with the registration code has been sent to ' . $code->phone_number . '.Please enter the code to confirm your phone number', 200);

    }

    public function activatePhoneOTP(Request $request)
    {

        $user = auth()->user();

        $kyc = true;

        // Begin for KYC Disable
        $role = $user->getRoles()->first();

        if ($role) {
            $roleData = Role::where('name', $role)->first();

            if ($roleData) {
                if ($roleData->kyc_status) {
                    $kyc = false;
                    $rules = [
                        'phone_number' => 'bail|required|numeric:12|unique:users,phone_number',
                    ];
                } else {
                    $rules = [
                        'phone_number' => 'bail|required|numeric:12|exists:codes,phone_number|unique:users,phone_number',
                        'code' => 'bail|required|exists:codes,code_phone'
                    ];
                }
            } else {
                $rules = [
                    'phone_number' => 'bail|required|numeric:12|exists:codes,phone_number|unique:users,phone_number',
                    'code' => 'bail|required|exists:codes,code_phone'
                ];
            }
        } else {
            $rules = [
                'phone_number' => 'bail|required|numeric:12|exists:codes,phone_number|unique:users,phone_number',
                'code' => 'bail|required|exists:codes,code_phone'
            ];
        }
        // End for KYC enable

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'error' => ['message' => $validator->errors()->all()]
            ], 422);
        }

        $phone = $request->phone_number;

        if ($kyc) {
            if (config('services.informa.phone_search_active')) {
                $valid = $this->searchByPhoneNId($phone, $user->id_number);
                if (!$valid) {
                    return $this->errorMessage(['error' => ['Your Identification document does not match the phone number provided'], 'code' => 422], 422);
                }
                $user->verified_phone = 1;
                $user->save();
            }
        }


        $code = Code::where('phone_number', $phone)
            ->where('code_phone', $request->code)
            ->first();
        if ($code) {
            $user->phone_number = $phone;
            $user->active = 1;
            $user->verified_otp = 1;
            $user->save();
        }
        if (!$kyc) {
            $user->verified_phone = 1;
            $user->phone_number = $request->phone_number;
            $user->verified_otp = 1;
            $user->active = 1;
            $user->save();
        }

        $code = Code::where('phone_number', $request->phone_number)->first();

        if ($code) {
            $code->delete();
        }

        return $this->successMessage('Account activated', 200);

    }

    public function activateUserAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_number' => 'bail|required|max:191|unique:users,id_number',
            'citizenship' => 'bail|required|in:kenyan,foreigner',
            'firstname' => 'bail|required|max:191',
            'middlename' => 'max:191',
            'lastname' => 'bail|required|max:191',
        ], ['id_number.unique' => 'Your details exist in the system reset your email password to login']);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'error' => ['message' => $validator->errors()->all()]
            ], 422);
        }

        $user = Auth::guard('api')->user();

        $resp = $this->searchById($request->all());


        if ($resp && $resp['valid']) {
            $resp['valid'] = 1;
        } else {
            $resp['valid'] = 0;
        }


        // Begin for KYC Disable
        $role = $user->getRoles()->first();

        if ($role) {
            $roleData = Role::where('name', $role)->first();

            if ($roleData) {
                if ($roleData->kyc_status) {
                    $user->id_number = $request->id_number;
                    $user->firstname = $request->firstname;
                    $user->lastname = $request->lastname;
                    $user->middlename = $request->middlename;
                    $user->save();

                    return $this->successMessage("Good Progress, $user->firstname, one more step to go", 200);
                }
            }
        }
        // End for KYC enable

        if ($resp['valid'] == 1) {

            if (strtoupper($resp['first_name']) != strtoupper($request->firstname)) {

                return $this->errorResponse('firstname provided does not match the registered first name', 422);
            }

            if (strtoupper($resp['last_name']) != strtoupper($request->lastname)) {

                return $this->errorResponse('The last name provided does not match the registered last name', 422);
            }

            $user->id_number = $resp['id_number'];
            $user->firstname = $resp['first_name'];
            $user->lastname = $resp['last_name'];
            $user->middlename = $resp['middle_name'];
            $user->kra_pin = $resp['kra_pin'];
            $user->save();

            return $this->successMessage("Good Progress, $user->firstname, one more step to go", 200);
        } else {
            return $this->errorResponse('You have entered an invalid id number', 422);
        }

    }


    public function logout(Request $request)
    {
        try {
            $token = $request->user()->token();
            $token->revoke();
        } catch (Exception $e) {

        }
        return $this->successResponse('You have been successfully logged out!');
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|max:191',
            'new_password' => 'bail|required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'code' => 'input_invalid',
                    'message' => $validator->errors()->all()
                ]
            ], 422);
        }

        $user = Auth::guard('api')->user();

        $old_password = Auth::guard('api')->user()->password;

        if (Hash::check($request->current_password, $old_password)) {
            $usr = User::findOrFail($user->id);

            $usr->password = Hash::make($request->new_password);
            $usr->requires_password_change = 0;

            $usr->save();

            return 'success';

        } else {
            return $this->errorMessage('Wrong Current Password, Please try again!', 400);
        }

    }

}
