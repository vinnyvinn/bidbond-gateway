<?php


namespace App\Traits;
use App\Services\CompanyService;
use Illuminate\Support\Facades\Auth;

class CompanyUser
{

    public static function getCompaniesForUser($user){
        $companies = CompanyService::initCompany()->obtainFilteredCompanies($user);
      return isset($companies) ? json_decode(CompanyService::initCompany()->obtainFilteredCompanies($user),true) : [];
    }
}
