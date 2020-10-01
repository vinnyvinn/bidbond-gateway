<?php

namespace App\Http\Controllers;

use App\Agent;
use App\Group;
use App\Mail\CompanyCreated;
use App\Services\BidBondService;
use App\Traits\ApiResponser;
use App\Traits\CompanyUser;
use App\Traits\coreBanking;
use App\Traits\KycCheck;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Services\CompanyService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Bouncer;
use App\User;
use App\Commission;
use Carbon\Carbon;
use App\Services\CounterPartyService;
use App\CompanyCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{

    use ApiResponser, KycCheck;

    public $companyService;
    public $counter_party_service;
    public $bidBondService;


    public function __construct(CompanyService $companyService, CounterPartyService $counter_party_service, BidBondService $bidBondService)
    {
        $this->companyService = $companyService;
        $this->counter_party_service = $counter_party_service;
        $this->bidBondService = $bidBondService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $page = $request->page ?? 1;

        if ($user->isAn('agent')) {

            $agent = $user->agent()->first();

            return response()->json(json_decode($this->bidBondService->getAgentCompanies($agent->secret, $page), true));

        }
        if (Bouncer::can('list-companies-owned')) {

            $companies = json_decode($this->companyService->obtainUserCompanies(['email' => $user->email, 'userid' => $user->id]), true);

            return response()->json(["data" => $companies, "from" => 1]);

        } elseif (Bouncer::can('list-companies')) {
            return response()->json(json_decode($this->companyService->obtainCompanies($request->all()), true));
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'crp' => 'required',
            'email' => 'bail|required|email',
            'name' => 'required',
            'phone_number' => 'bail|required|min:10|max:15',
            'physical_address' => 'required',
            'postal_address' => 'required',
            'postal_code_id' => 'required',
            'kra_pin' => 'required',
            'relationship_manager_id' => 'sometimes|numeric'
        ]);

        $auth_user = auth()->user();

        $data = $request->only([
            'crp', 'email', 'name', 'phone_number', 'physical_address', 'relationship_manager_id',
            'postal_address', 'postal_code_id','kra_pin'
        ]);

        $group = Group::ofName('customer')->first();

        $data = array_merge($data, [
            'group_id' => $group->id,
            'user' => $auth_user->id,
            'kyc_status' => $this->kycEnabled(),
        ]);

        $company_response = $this->companyService->store($data);

        $company = json_decode($company_response, true);

        if ($company['code'] !== 200) {
            return $this->errorResponse($company['error'], $company['code']);
        }

        $company = $company['company'];

        if ($auth_user->referral_code) {

            $new_user = User::where('user_unique_id', $auth_user->referral_code)->first();

            if ($new_user) {
                $this->commission($new_user);
            }

        } else {
            $this->commission($auth_user);
        }

        if (!$this->kycEnabled() && !$auth_user->isAn('agent')) {

            $company = json_decode($company_response)->company;

            $response = $this->sendCompanyCreatedNotification($company);

            return response()->json($response["message"], $response["code"]);
        }

        return $this->successResponse($company, Response::HTTP_CREATED);
    }

    public function getBalance($user,$role)
    {
     return response()->json(coreBanking::invokeAccount($user,$role));
    }

    public function createAccount($company)
    {
      return coreBanking::registerNewAccount($company);
    }

    public function getDeletedCompanies()
    {
        return response()->json(json_decode($this->companyService->getDeletedCompanies(), true));
    }

    public function approvedCompanyOptions()
    {
        if (Bouncer::can('list-companies-owned')) {

            $user = auth()->user();

            if ($user->isAn('agent')) {

                $agent = $user->agent()->first();

                return $this->successResponse(json_decode($this->bidBondService->getAgentCompanyOptions($agent->secret)));

            } else {

                return $this->successResponse(
                    json_decode(
                        $this->companyService->obtainApprovedUserCompanyOptions(['email' => $user->email, 'userid' => $user->id])
                    )
                );

            }

        } else if (Bouncer::can('list-companies')) {

            return $this->successResponse(json_decode($this->companyService->obtainApprovedCompanyOptions()));
        }
    }

    public function getPostalCodes()
    {
        $postalcodes = Cache::rememberForever('postal_codes', function () {
            return json_decode($this->companyService->obtainPostalCodes(), true);
        });

        return response()->json($postalcodes);
    }

    public function getCompanyFromBidService($company_id)
    {
        $response = json_decode($this->bidBondService->getBidCompanyDetail($company_id), true);
        $user = auth()->user();
        if ($user->isAn('agent')) {
            $agent = $user->agent()->first();
            $response['balance'] = $agent->balance;
            $response['limit'] = $agent->limit;
        }
        return response()->json($response);
    }

    public function approveCompany(Request $request)
    {
        $code = CompanyCode::where('code', $request->code)->first();

        if ($code) {

            $this->companyService->approve($code->company_id);

            $code->delete();

            return $this->successResponse('Approved');
        }

        return $this->errorMessage('Code not valid!', 422);
    }

    public function getCompanyById(Request $request)
    {
       return json_decode($this->companyService->getCompanyByUnique($request->company),true);
    }

    public function approveByAdmin(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required|max:191'
        ]);

        $company = json_decode($this->companyService->getCompanyByUnique($request->company_id));

        if ($company->approval_status == 'approved') {
            return response()->json("Company already approved", 422);
        }

        $response = $this->sendCompanyCreatedNotification($company);

        return response()->json($response["message"], $response["code"]);

    }

    protected function sendCompanyCreatedNotification($company): array
    {
        $user = User::where("email", $company->email)->first();

        $password = null;

        if (!$user) {
            $password = getToken(6);

            $user = User::create([
                'email' => $company->email,
                'password' => bcrypt($password),
                'requires_password_change' => true,
                'parent' => auth()->id()
            ]);
        }
        $company = json_decode($this->companyService->approveCompanyByAdmin($company->company_unique_id, $user->id),true);

        if ($company['code'] !== 200) {
            return array("message" => $company['error'], "code" => $company['code']);
        }

        $company = $company['company'];
        Mail::to($user)->queue(new CompanyCreated($company, $company['email'], $password));

        return array("message" => "Company approval successful", "code" => 200);
    }

    protected function commission($user)
    {
        $role = $user->roles()->first();

        if (!$role) {
            return;
        }

        $commission = Commission::where('role_id', $role->id)->first();

        if (!$commission) {
            return;
        }

        if ($commission->commision_type !== 'company') {
            return;
        }

        $amount = ($commission->amount);

        $employment_date = Carbon::parse($user->created_at);

        $last_period_date = $employment_date;

        $today = Carbon::now();

        while ($last_period_date->diffInDays($today) >= $commission->period) {
            $last_period_date->addDays($commission->period);
        }

        $no_of_companies = $this->companyService->countUserCompanies([
            'userid' => $user->id,
            'last_period_date' => $last_period_date
        ]);

        if ($no_of_companies >= $commission->target) {
            DB::table('row_commisions')->insert([
                'user_id' => $user->id,
                'commission_amount' => $amount,
                'commission_type' => $commission->commision_type,
                'details' => json_encode([
                    'no_of_companies' => $no_of_companies,
                    'last_period_date' => $last_period_date,
                    'new_period_date' => $last_period_date,
                    'target' => $commission->target
                ])
            ]);
        }
    }

    public function uploadCompanyDocument(Request $request)
    {
        $this->validate($request, ['company_document' => 'required|file |mimes:pdf,jpg,jpeg,png,docx']);

        $user = auth()->user();

        $filename = $this->storeDocument($request->company_document, 'Document');

        $data = $request->all();

        $data['filename'] = $filename;

        $data['user_id'] = $user->id;

        return $this->companyService->addFileToCompany($data);
    }

    protected function storeDocument($doc, $title)
    {
        $destinationPath = storage_path('app/Company');

        if ($doc->isValid()) {

            $extension = $doc->getClientOriginalExtension();

            $fileName = safeFileName($title . '-' . date('Ymd H is') . '.' . $extension);

            $doc->move($destinationPath, $fileName);

            return $fileName;

        } else {
            return $this->errorMessage('Document upload failed', 400);
        }
    }

    public function show($company)
    {
        $company_details = json_decode($this->companyService->obtainCompany($company), true);

        $company_details['limit'] = 0;

        $company_details['balance'] = 0;

        $group = Group::findOrFail($company_details['data']['group_id']);

        $company_details['group'] = $group->name;

        if ($company_details['data']['approval_status'] == 'approved') {

            $bid_company_details = json_decode($this->bidBondService->getCompany($company), true);

            $company_details['limit'] = isset($bid_company_details['limit']) ? $bid_company_details['limit'] : 0;

            $company_details['balance'] = isset($bid_company_details['balance']) ? $bid_company_details['balance'] : 0;
        }
        if (Arr::has($company_details, 'error')) {
            return response()->json($company_details, $company_details['code']);
        }

        $company_details['data']['directors'] = collect($company_details['data']['directors'])->map(function ($dir) {

            $user = User::where('email', $dir['email'])->first();

            if ($user) {
                $dir['director_unique_id'] = $user->user_unique_id;
            }

            return $dir;
        });

        return $this->successResponse($company_details);
    }

    public function downloadResolution()
    {
        return config('app.url') . '/docs/resolution.docx';
    }

    public function update(Request $request, $company)
    {
        return $this->companyService->edit($request->all(), $company);
    }

    public function destroy($company_id)
    {
        return $this->companyService->delete($company_id);
    }

    public function restore($company_id)
    {
        return $this->companyService->restore($company_id);
    }

    public function brsSearch($companyid)
    {
        $user = auth()->user();

        return $this->companyService->searchCompany([
            'firstname' => $user->firstname,
            'middlename' => $user->middlename,
            'lastname' => $user->lastname,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'id_number' => $user->id_number,
            'companyid' => $companyid,
            'kyc_status' => $this->kycEnabled()
        ]);
    }

    public function CompanySearch(Request $request)
    {
        return $this->companyService->searchCompanyByName($request->all());
    }

    public function updateLimit(Request $request)
    {
        $response = json_decode($this->bidBondService->updateCompanyLimit($request->all()), true);
        return response()->json($response, $response["code"]);
    }

    public function checkAndApproveCompany(Request $request)
    {
        $request->validate([
            'crp' => 'required',
            'company_id' => 'required'
        ]);

        $response = json_decode($this->companyService->checkApprovalStatus(['crp' => $request->crp, 'company_id' => $request->company_id]), true);

        return response()->json($response, $response['code']);

    }

    public function attachments(Request $request)
    {
        $request->validate([
            'company_id' => 'required',
            'path' => 'required',
        ]);

        if (!Bouncer::can('download-documents')) {
            $this->errorResponse('You do not have the rights to view this document', 403);
        }

        if (!Storage::exists($request->path)) {
            $this->errorResponse('Attachment not found', 404);
        }

        $user = auth()->user();

        if ($user->isAn('customer')) {

            $is_company_user = $this->companyService->getCompanyUser($request->company_id, $user->id);

            if (!$is_company_user) {
                $this->errorResponse('You do not have the rights to view this document', 403);
            }
        }

        return response()->download(storage_path($request->path));
    }

}
