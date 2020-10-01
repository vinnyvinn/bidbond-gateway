<?php

namespace App\Http\Controllers;

use App\Services\BidBondService;
use App\Traits\ApiResponser;
use App\Services\CompanyService;
use App\Services\CounterPartyService;
use App\Quote;
use Bouncer;
use Illuminate\Support\Facades\Cache;



class DashboardController extends Controller
{


    public $bidBondService, $company_service, $counter_party_service;

    use ApiResponser;

    public function __construct(BidBondService $bidBondService, CompanyService $company_service, CounterPartyService $counter_party_service)
    {
        $this->bidBondService = $bidBondService;
        $this->company_service = $company_service;
        $this->counter_party_service = $counter_party_service;
    }

    public function getStats()
    {

        $user = auth()->user();
        $quotes = 0;
        if (Bouncer::can("list-quotes-owned")) {
            $quotes = Quote::where('email', $user->email)->count();
        }

        if (Bouncer::can("list-quotes")) {
            $quotes = Cache::remember('users', 1800, function () {
                return Quote::count();
            });
        }
        $parties = json_decode($this->counter_party_service->getCount(), true)['data'];

        $userbidbonds = [];
        $companies = [];
        if (Bouncer::can("list-companies-owned")) {
            if (!$user->isAn('agent')) {
                $companies = json_decode($this->company_service->obtainUserCompanies(['email' => $user->email, 'userid' => $user->id, 'per_page' => 5]), true);
            }else{
                $agent = $user->agent()->first();
                $companies = json_decode($this->bidBondService->getAgentCompanies($agent->secret, 1), true);
                $companies ? $companies = $companies['data'] : $companies = [];
            }

        } else if (Bouncer::can("list-companies")) {
            $companies = json_decode($this->company_service->obtainApprovedCompanies(['per_page' => 5]), true);

            $companies ? $companies = $companies['data'] : $companies = [];
        }

        if (Bouncer::can("list-bidbonds-owned")) {
            if (count($companies) > 0) {

                $userbidbonds = json_decode($this->bidBondService->obtainUserBidBonds([
                    "company_unique_id" => collect($companies)->pluck('company_unique_id')->all()
                ]), true)['data'];
            }

        } else if (Bouncer::can("list-bidbonds")) {
            $userbidbonds = json_decode($this->bidBondService->obtainBidBonds(['per_page' => 5]), true)['data'];
        }

        $stats['bidbonds'] = $userbidbonds;
        $stats['companies'] = $companies;
        $stats['quotes'] = (int)$quotes;
        $stats['counter_parties'] = $parties;
        $stats['user'] = $user;

        return $this->successResponse($stats, 200);

    }
}
