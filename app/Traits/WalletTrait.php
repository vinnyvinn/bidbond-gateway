<?php

namespace App\Traits;


use App\Services\AgentWalletService;
use App\Services\BidBondService;
use App\Services\CompanyService;
use App\Services\CompanyWalletService;

trait WalletTrait
{

    /**
     * @param CompanyService $companyService
     * @param CompanyWalletService $companyWalletService
     * @return array
     */
    function userCompanyWalletBalance(CompanyService $companyService, CompanyWalletService $companyWalletService): array
    {
        $is_company_user = $companyService->getCompanyUser($companyWalletService->company_id, auth()->id());

        if (!$is_company_user) {
            return [
                'status' => 'error',
                'error' => ['message' => 'User is not linked to the company']
            ];
        }
        info($companyWalletService->company_id . 'companyWalletService->company_id');
        return $companyWalletService->getBalance();
    }


    function agentWalletBalance($company_id, AgentWalletService $agentWalletService): array
    {
        $bidBondService = new BidBondService();

        $is_agent_user = json_decode($bidBondService->getAgentCompany($agentWalletService->secret, $company_id));

        if (!$is_agent_user) {
            return [
                'status' => 'error',
                'error' => ['message' => 'Agent is not linked to the company']
            ];
        }

        return $agentWalletService->getBalance();
    }

}
