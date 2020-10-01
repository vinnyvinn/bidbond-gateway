<?php

namespace App\Services;

use App\Traits\ConsumesExternalService;


class CategoryService
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
    return $this->performRequest('GET', '/categories');
  }

  public function create($data)
  {
    return $this->performRequest('POST', '/categories', $data);
  }

  public function show($secret)
  {
    return $this->performRequest('get', '/categories/' . $secret);
  }

  public function edit($secret)
  {
    return $this->performRequest('get', '/categories/' . $secret . '/edit');
  }

  public function update($data, $secret)
  {
    return $this->performRequest('PUT', '/categories/' . $secret, $data);
  }

  public function destroy($secret)
  {
    return $this->performRequest('POST', '/categories/' . $secret);
  }
}
