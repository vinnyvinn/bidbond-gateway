<?php

namespace App\Services;

use App\Traits\ConsumesExternalService;


class CompanyService
{

    use ConsumesExternalService;

    public $baseUri;

    public $secret;


    public function __construct()
    {
        $this->baseUri = config('services.companies.base_uri');

        $this->secret = config('services.companies.secret');
    }
    public static function initCompany(){
     return new self();
   }

    public function approve($company_id)
    {
        return $this->performRequest('POST', "/approve-company/{$company_id}");
    }

    public function approveCompanyByAdmin($company_id, $user_id)
    {
        return $this->performRequest('POST', "/approve-company-by-admin/{$company_id}", compact('user_id'));
    }

    public function obtainCompanies($data)
    {
        return $this->performRequest('GET', '/companies', $data);
    }
    public function obtainFilteredCompanies($user_id)
    {
        return $this->performRequest('POST', "/company/filter-companies/{$user_id}");
    }

    public function getDeletedCompanies()
    {
        return $this->performRequest('GET', '/companies/deleted');
    }

    public function obtainApprovedCompanies($data)
    {
        return $this->performRequest('GET', '/approved-companies', $data);
    }
    public function obtainAllCompanies()
    {
        return $this->performRequest('GET', '/all-companies');
    }

    public function obtainApprovedCompanyOptions()
    {
        return $this->performRequest('GET', '/approved-companies/options');
    }

    public function getCompanyUsers($company_id)
    {
        return $this->performRequest('GET', "/company/" . $company_id . "/users");
    }

    public function getCompanyUser($company_unique_id, $user_id)
    {
        return $this->performRequest('GET', "/company/" . $company_unique_id . "/" . $user_id);
    }

    public function obtainUserCompanies($data)
    {
        return $this->performRequest('POST', "/getUserCompanies", $data);
    }

    public function obtainApprovedUserCompanies($data)
    {
        return $this->performRequest('POST', "/getApprovedUserCompanies", $data);
    }

    public function obtainApprovedUserCompanyOptions($data)
    {
        return $this->performRequest('POST', "/approved-companies/user/options", $data);
    }

    public function obtainPostalCodes()
    {
        return $this->performRequest('GET', '/postalcodes');
    }

    public function store($data)
    {
        return $this->performRequest('POST', '/companies', $data);
    }

    public function obtainCompany($company)
    {
        return $this->performRequest('GET', "/companies/{$company}");
    }

    public function getCompanyByUnique($unique)
    {
        return $this->performRequest('GET', "/companies/unique/{$unique}");
    }

    public function edit($data, $company)
    {
        return $this->performRequest('PUT', "/companies/{$company}", $data);
    }

    public function delete($company)
    {
        return $this->performRequest('DELETE', "/companies/{$company}");
    }

    public function restore($company)
    {
        return $this->performRequest('POST', "/companies/{$company}restore");
    }

    public function searchCompany($data)
    {
        return $this->performRequest('POST', "/companysearch", $data);
    }

    public function searchCompanyByName($data)
    {
        return $this->performRequest('POST', "/companysearch-by-name", $data);
    }

    public function createDirector($data)
    {
        return $this->performRequest('POST', "/create-director", $data);
    }

    public function manualCreate($data)
    {
        return $this->performRequest('POST', "/manualCreate", $data);
    }

    public function attachUser($data)
    {
        return $this->performRequest('POST', "/attach-user", $data);
    }

    public function detachUser($data)
    {
        return $this->performRequest('POST', "/detach-user", $data);
    }

    public function getUserId($companyid)
    {
        return $this->performRequest('GET', "/getUserId/{$companyid}");
    }

    public function getCompaniesById($data)
    {
        return $this->performRequest('POST', "companies/company_id", $data);
    }

    public function getCompanyByRegistractionNumber($data)
    {
        return $this->performRequest('POST', "company/byregistration", $data);
    }

    public function countUserCompanies($data)
    {
        return $this->performRequest('POST', '/countUserCompanies', $data);
    }

    public function addFileToCompany($data)
    {
        return $this->performRequest('POST', "/add-file-to-company", $data);
    }

    public function updateDirector($data)
    {
        return $this->performRequest('POST', "/update-director", $data);
    }

    //marketing services
    public function createGroup($data)
    {
        return $this->performRequest('POST', "/create-group", $data);
    }

    public function attachGroup($data)
    {
        return $this->performRequest('POST', "/attach-group", $data);
    }

    public function detachGroup($data)
    {
        return $this->performRequest('POST', "/detach-group", $data);
    }

    public function listGroups()
    {
        return $this->performRequest('GET', "/list-groups");
    }

    public function companyByGroupId($id)
    {
        return $this->performRequest('GET', "/companies-by-groupid/" . $id);
    }

    public function checkApprovalStatus($data)
    {
        return $this->performRequest('GET', "/check-company-approval", $data);
    }

    public function directorCompanyApproval($data)
    {
        return $this->performRequest('GET', "/director-company-approval", $data);
    }


    public function getCompanyDirectors($id)
    {
        return $this->performRequest('GET', "/company-directors/" . $id);
    }

    public function getCompanyById($id)
    {
        return $this->performRequest('GET', "/company-by-id/" . $id);
    }

    public function updateDirectorDetails($data)
    {
        return $this->performRequest('POST', "/update-director-details", $data);
    }

    public function updatePaymentStatus($data)
    {
        return $this->performRequest('POST', "/update-payment-status", $data);
    }

    public function VerifyDirectorSms($data)
    {
        return $this->performRequest('POST', "/verify-company-director", $data);
    }

    public function migrate()
    {
        return $this->performRequest('get', '/migrate');
    }

}
