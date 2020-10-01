<?php

namespace App\Services;

use App\Traits\ConsumesExternalService;


class CounterPartyService
{

  use ConsumesExternalService;

  public $baseUri;

  public $secret;


  public function __construct()
  {
    $this->baseUri = config('services.bidbonds.base_uri');
    $this->secret = config('services.bidbonds.secret');
  }

  public function index()
  {
    return $this->performRequest('GET', '/counter-parties');
  }

    public function getCount()
    {
        return $this->performRequest('GET', '/counter-parties/count');
    }

  public function getCategoriesNPostalCodes()
  {
    return $this->performRequest('GET', '/createcounterdetails');
  }


  public function destroy($id)
  {
    return $this->performRequest('POST', '/counter-parties/'.$id);
  }

  public function store($data)
  {
    return $this->performRequest('POST', '/counter-parties', $data);
  }

    public function update($data,$id)
    {
        return $this->performRequest('PUT', '/counter-parties/'.$id, $data);
    }

  public function show($id)
  {
    return $this->performRequest('GET', '/counter-parties/'.$id);
  }

}
