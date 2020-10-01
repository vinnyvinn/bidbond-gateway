<?php

namespace App\Services;

use App\Traits\ConsumesExternalService;


class BondPricingService
{

  use ConsumesExternalService;

  public $baseUri;

  public $secret;


  public function __construct()
  {
    $this->baseUri = config('services.bidbonds.base_uri');
    $this->secret = config('services.bidbonds.secret');
  }

  public function obtainBondPricings()
  {
    return $this->performRequest('GET', '/bid-bond-pricing');
  }

  public function createBondPricing($data)
  {
    return $this->performRequest('POST', '/bid-bond-pricing', $data);
  }

  public function previewBondPricing($id)
  {
    return $this->performRequest('get', '/bid-bond-pricing/' . $id);
  }

  public function editBondPricing($id)
  {
    return $this->performRequest('get', '/bid-bond-pricing/' . $id . '/edit');
  }

  public function updateBondPricing($data, $id)
  {
    return $this->performRequest('PUT', '/bid-bond-pricing/' . $id, $data);
  }

  public function deleteBondPricing($id)
  {
    return $this->performRequest('delete', '/bid-bond-pricing/' . $id);
  }

  public function paymentReport()
  {
    return $this->performRequest('post','/payment-report-data');
  }

}

