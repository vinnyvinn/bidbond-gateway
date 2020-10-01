<?php

namespace App\Services;

use App\Traits\ConsumesExternalService;


class BidBondService
{

    use ConsumesExternalService;

    public $baseUri;

    public $secret;


    public function __construct()
    {
        $this->baseUri = config('services.bidbonds.base_uri');
        $this->secret = config('services.bidbonds.secret');
    }
    public static function init(){
        return new self();
    }
    public function getBidCount()
    {
        return $this->performRequest('GET', '/bid-bonds/count');
    }

    public function obtainBidBonds($data)
    {
        return $this->performRequest('GET', '/bid-bonds', $data);
    }

    public function createBidBond($data)
    {
        return $this->performRequest('POST', '/bid-bonds', $data);
    }

    public function createCompany($data)
    {
        return $this->performRequest('POST', '/companies', $data);
    }

    public function updateCompany($data)
    {
        return $this->performRequest('PUT', '/companies', $data);
    }

    public function deleteCompany($data)
    {
        return $this->performRequest('DELETE', '/companies', $data);
    }

    public function getCompany($id)
    {
        return $this->performRequest('GET', '/companies/' . $id);
    }

    public function updateCompanyLimit($data)
    {
        return $this->performRequest('POST', '/company/limits', $data);
    }

    public function getAgentCompanies($secret, $page)
    {
        return $this->performRequest('GET', $secret . '/companies?page=' . $page);
    }

    public function getAgentCompany($agent_id, $company_id)
    {
        return $this->performRequest('GET', 'agent/company/' . $agent_id . '/' . $company_id);
    }

    public function setAgentLimits($data)
    {
        return $this->performRequest('POST', '/agent/limits', $data);
    }

    public function updateAgentLimits($data)
    {
        return $this->performRequest('PUT', '/agent/limits', $data);
    }

    public function getAgentCompanyOptions($secret)
    {
        return $this->performRequest('GET', $secret . '/companies/options');
    }

    public function getBidCompanyDetail($id)
    {
        return $this->performRequest('GET', '/companies/' . $id);
    }

    public function getAgentBidCompanies($page)
    {
        return $this->performRequest('GET', '/agent/companies?page=' . $page);
    }

    public function previewBidBond($data)
    {
        return $this->performRequest('get', '/bid-bonds/' . $data['secret']);
    }

    public function preview($data)
    {
        return $this->performRequest('POST', '/bid-bond-templates/preview', $data);
    }

    public function getBidBond($data)
    {
        return $this->performRequest('get', '/get-bond/' . $data['secret']);
    }

    public function getById($id)
    {
        return $this->performRequest('GET', '/bid-bonds/id/' . $id);
    }
    public function getByTender($tender)
    {
        return $this->performRequest('POST', '/get-bond-by-tender', $tender);
    }

    public function getBidBondsBySecret($data)
    {
        return $this->performRequest('post', '/bid-bonds/secret', $data);
    }

    public function obtainUserBidBonds($data)
    {
        return $this->performRequest('post', '/obtainUserBidBonds', $data);
    }

    public function obtainBidBondsByUser($data)
    {
        return $this->performRequest('post', '/user/bid-bonds', $data);
    }

    public function obtainBidBondsByAgent($data)
    {
        return $this->performRequest('post', '/agent/bid-bonds', $data);
    }

    public function obtainSettings()
    {
        return $this->performRequest('GET', '/settings');
    }

    public function updateSettings($data)
    {
        return $this->performRequest('post', '/settings', $data);
    }
    public function applyBid($data)
    {
        return $this->performRequest('post', '/apply-bidbond', $data);
    }

    public function updateBidbond($data)
    {
        return $this->performRequest('PUT', '/update-bidbonds', $data);
    }

    public function update($id, $data)
    {
        return $this->performRequest('PUT', '/bid-bonds/' . $id, $data);
    }

    public function countBidBonds($companies)
    {
        return $this->performRequest('post', '/count-bidbonds', $companies);
    }

    public function getTenderInfo($data)
    {
     return $this->performRequest('post', '/tender-info', $data);
    }

    public function migrate()
    {
        return $this->performRequest('get', '/migrate');
    }


}
