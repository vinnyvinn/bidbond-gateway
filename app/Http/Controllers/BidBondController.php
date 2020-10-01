<?php

namespace App\Http\Controllers;

use App\Agent;
use App\BidbondPrice;
use App\Jobs\GenerateBidPdf;
use App\RowCommision;
use App\Traits\coreBanking;
use App\Traits\ProcessBond;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use App\Services\BidBondService;
use App\Traits\ApiResponser;
use App\Services\CompanyService;
use App\Services\CounterPartyService;
use Illuminate\Support\Facades\Auth;
use Bouncer;
use PDF;
use App\Commission;
use Illuminate\Support\Facades\Storage;

class BidBondController extends Controller
{
    public $bidBondService, $companyService, $counter_party_service;

    use ApiResponser, ProcessBond;

    public function __construct(BidBondService $bidBondService, CompanyService $company_service, CounterPartyService $counter_party_service)
    {
        $this->bidBondService = $bidBondService;
        $this->companyService = $company_service;
        $this->counter_party_service = $counter_party_service;
    }

    public function index(Request $request)
    {
        if (Bouncer::can('list-bidbonds-owned')) {

            $user = Auth::guard('api')->user();

            if ($user->isAn('agent')) {
                //get agency users
                $agent = $user->agent->first();

                $user_ids = $agent->users()->pluck('user_id')->toArray();

                return $this->successResponse(
                    json_decode($this->bidBondService->obtainBidBondsByUser(compact('user_ids')), true)
                );

            } else {

                $companies = collect(
                    json_decode($this->companyService->obtainUserCompanies([
                        'email' => $user->email,
                        'userid' => $user->id]), true)
                );

                if (count($companies) == 0) {
                    return $this->successResponse([]);
                }

                return $this->successResponse(
                    json_decode($this->bidBondService->obtainUserBidBonds([
                        'company_unique_id' => $companies->pluck('company_unique_id')->all()
                    ]), true)
                );
            }

        } else if (Bouncer::can('list-bidbonds')) {
            $bidbonds = json_decode($this->bidBondService->obtainBidBonds($request->all()), true);

            return $this->successResponse($bidbonds);
        }

    }

    public function store(Request $request)
    {
        $data = $request->all();

        $user = auth()->user();

        $role = $user->roles()->first();

        if ($role->name !== "agent") {
            $company = json_decode($this->companyService->getCompanyByUnique($request->company));
            $group_id = $company->group_id;
            $account = $company->account;

        } else {
            $agent = $user->agent()->first();
            $group_id = $agent->group_id;
            $account = $agent->account;
            $data["agent_id"] = $agent->secret;

            if (!$this->agentWithinLimit($agent, $request)) {
                return response()->json([
                    'status' => 'error',
                    'error' => ['message' => 'You have exceeded your bid bond limit quota. Please contact your account manager']
                ], 400);
            }
        }

        $bidbond_breakdown = BidbondPrice::getBreakdown($data['amount'], $data['period'], $group_id);

        $data = array_merge($data, [
            'created_by' => $user->id,
            'charge' => $bidbond_breakdown['total'],
        ]);

        $bidbond = json_decode($this->bidBondService->createBidBond($data), true);
        $bidbond['account'] = $account;

        $this->handleCommission($role, $bidbond, $user, $bidbond_breakdown["bid_bond_charge"]);

        return $this->successResponse($bidbond);
    }

    protected function agentWithinLimit(Agent $agent, Request $request): bool
    {
        if ($agent->balance == $request->amount) return true;
        return $agent->balance > $request->amount;
    }


    private function handleCommission($role, $bidbond, ?Authenticatable $user, $charge): void
    {
        $commission = Commission::role($role->id)->first();

        if (!$commission) {
            return;
        }

        $amount = ($commission->amount / 100) * $charge;

        RowCommision::create([
            'user_id' => $user->id,
            'commission_amount' => $amount,
            'commission_type' => 'bidbond',
            'bidbond_id' => $bidbond['data']['id']
        ]);
    }

    public function downloadBidbond(Request $request)
    {
        if (!Storage::exists('public/' . $request->secret . '.pdf')) {
            $bidbond = json_decode($this->bidBondService->getBidBond(['secret' => $request->secret]))->data;
            $bidbond->secret = $request->secret;
            $bidbond->created_by = $bidbond->userid;
            dispatch(new GenerateBidPdf($bidbond));
        }
        return config('app.url') . '/storage/' . $request->secret . '.pdf';
    }

    public function show($secret)
    {
        return view('bidbond.complete', ['content' => $this->bidBondService->previewBidBond(compact('secret'))]);
    }

    public function update($id, Request $request)
    {
        $bid_response = $this->bidBondService->update($id, $request->all());
        $bidbond = json_decode($bid_response)->data;
        $bid_array = json_decode($bid_response, true)['data'];
        // Regenerate only if paid for i.e. bid bond pdf exists
        if ($bidbond->paid == 1) {
            dispatch(new GenerateBidPdf($bidbond));
        }
        return $this->successResponse($bid_array, 201);
    }

    public function preview(Request $request)
    {
        return $this->bidBondService->preview($request->all());
    }

    public function getById($id)
    {
        return $this->bidBondService->getById($id);
    }

    public function getByTender(Request $request)
    {
        return $this->bidBondService->getByTender($request->all());
    }

    public function applyBidBond(Request $request)
    {
        $ref = coreBanking::applyBidbond($request->company, $request->tender_no, $request->total, $request->user, $request->role);
        $updated_ref = $this->bidBondService->applyBid(['reference' => $ref, 'tender_no' => $request->tender_no]);
        return response()->json('success');
    }

    public function getPricing(Request $request)
    {
        $request->validate([
            'amount' => 'bail|numeric|required',
            'period' => 'bail|numeric|required',
            'company_id' => 'required',
            'secret' => 'sometimes'
        ]);

        if ($request->has('secret')) {
            $bidbond = json_decode($this->bidBondService->getBidBond(['secret' => $request->secret]))->data;
            if ($bidbond->agent_id) {
                $group_id = Agent::secret($bidbond->agent_id)->first()->group_id;
            } else {
                $group_id = json_decode($this->companyService->getCompanyByUnique($bidbond->company_id))->group_id;
            }
        } else {
            $user = auth()->user();
            if (!$user->isAn("agent")) {
                $group_id = json_decode($this->companyService->getCompanyByUnique($request->company_id))->group_id;
            } else {
                $agent = $user->agent()->first();
                $group_id = $agent->group_id;
            }
        }

        return response()->json(BidbondPrice::getBreakdown($request->amount, $request->period, $group_id));
    }

    public function updateBidBond(Request $request)
    {
        $bid_response = $this->bidBondService->updateBidbond($request->all());
        $bidbond = json_decode($bid_response)->data;
        // Regenerate only if paid for i.e. bid bond pdf exists
        if (Storage::exists('public/' . $bidbond->secret . '.pdf')) {
            dispatch(new GenerateBidPdf($bidbond));
        }
        $bid_array = json_decode($bid_response, true)['data'];
        return $this->successResponse($bid_array, 201);
    }


    public function getTenderInfo(Request $request)
    {
        return $this->bidBondService->getTenderInfo($request->all());
    }

    public function bidBondCost(Request $request)
    {
        $bidbond = json_decode($this->bidBondService->getBidBond(['secret' => $request->secret]))->data;

        if ($bidbond->agent_id) {
            $agent = Agent::secret($bidbond->agent_id)->firstOrFail();
            $group_id = $agent->group_id;
            $account = $agent->account;
        } else {
            $company = json_decode($this->companyService->getCompanyByUnique($bidbond->company_id));
            $group_id = $company->group_id;
            $account = $company->account;
        }

        $tender = $bidbond->tender_number;
        $pricing = BidbondPrice::getBreakdown($bidbond->tender_amount, $bidbond->tender_period, $group_id);
        return $this->successResponse(['tender' => $tender, 'pricing' => $pricing, 'account' => $account]);
    }

    public function checkExists(Request $request)
    {
        return response()->json(['exists' => Storage::exists('public/' . $request->secret . '.pdf')], 200);
    }

}
