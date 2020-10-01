<?php

namespace App\Http\Controllers;

use App\Services\BidBondService;
use App\Services\CompanyService;
use Illuminate\Http\Request;

class AgentCompanyController extends Controller
{
    public $bidBondService;
    public $companyService;

    public function __construct(BidBondService $bidBondService,CompanyService $companyService)
    {
        $this->bidBondService = $bidBondService;
        $this->companyService = $companyService;
    }

    public function index(Request $request)
    {
        return $this->bidBondService->getAgentBidCompanies($request->page ?? 1);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $companies = collect(json_decode($this->companyService->searchCompanyByName($data), true));

        if ($companies->count() > 0) {

            $company = $companies->where('name', $data['name'])->first();

            if ($company) {
                $data['id'] = $company->id;
            }
        }

        $user = auth()->user();

        $agent = $user->agent()->firstOrFail();

        $data = array_merge($data, ['agent_id' => $agent->secret, 'type' => 'agent']);

        $company = json_decode($this->bidBondService->createCompany($data), true);

        return response()->json($company, $company['code']);
    }

    public function update(Request $request)
    {
        $data = $request->all();

        $companies = collect(json_decode($this->companyService->searchCompanyByName($data), true));

        if ($companies->count() > 0) {

            $company = $companies->where('name', $data['name'])->first();

            if ($company) {
                $data['existing_id'] = $company->id;
            }
        }

        $user = auth()->user();

        $agent = $user->agent()->firstOrFail();

        $data = array_merge($data, ['agent_id' => $agent->secret, 'type' => 'agent']);

        $company = json_decode($this->bidBondService->updateCompany($data), true);

        return response()->json($company, $company['code']);
    }

    public function destroy(Request $request)
    {
        $data = $request->all();

        $user = auth()->user();

        $agent = $user->agent()->firstOrFail();

        $data['agent_id'] = $agent->secret;

        $company = json_decode($this->bidBondService->deleteCompany($data), true);

        return response()->json($company, $company['code']);
    }

}
