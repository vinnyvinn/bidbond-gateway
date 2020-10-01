<?php

namespace App\Services;

use App\Traits\ConsumesExternalService;

class ReportsService
{

    use ConsumesExternalService;

    public $baseUri;

    public $secret;
    public static function initService(){
        return new self();
    }

    public function __construct()
    {
        $this->baseUri = config('services.reports.base_uri');
        $this->secret = config('services.reports.secret');
    }

    public function migrate()
    {
        return $this->performRequest('get', '/api/migrate');
    }

    public function bidbond_summary($data)
    {
        return $this->performRequest('GET', '/api/bidbonds/summary', $data);
    }

    public function company_summary($data)
    {
        return $this->performRequest('GET', '/api/bidbonds/company-summary', $data);
    }

    public function expired_bidbonds($data)
    {
        return $this->performRequest('GET', '/api/bidbonds/expired', $data);
    }

    public function dashboard()
    {
      return $this->performRequest('GET','/reports/dashboard');
    }
    public function companies()
    {
        return $this->performRequest('GET','/reports/companies');
    }
    public function bidbonds()
    {
        return $this->performRequest('GET','/reports/bidbonds');
    }
}
