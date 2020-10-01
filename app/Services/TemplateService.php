<?php

namespace App\Services;

use App\Traits\ConsumesExternalService;


class TemplateService
{

  use ConsumesExternalService;

  public $baseUri;

  public $secret;


  public function __construct()
  {
    $this->baseUri = config('services.bidbonds.base_uri');
    $this->secret = config('services.bidbonds.secret');
  }

  public function obtainTemplates($page)
  {
    return $this->performRequest('GET', '/bid-bond-templates?page='.$page);
  }

  public function createTemplate($data)
  {
    return $this->performRequest('POST', '/bid-bond-templates', $data);
  }

  public function previewTemplate($secret)
  {
      info('---here');
    return $this->performRequest('get', '/bid-bond-templates/'.$secret);
  }

  public function getParams()
  {
    return $this->performRequest('get', '/bid-bond-templates/create');
  }

  public function editTemplate($secret)
  {
    return $this->performRequest('get', '/bid-bond-templates/'.$secret.'/edit');
  }

  public function updateTemplate($data, $secret)
  {
    return $this->performRequest('PUT', '/bid-bond-templates/'.$secret, $data);
  }

  public function deleteTemplate($secret)
  {
    return $this->performRequest('POST', '/bid-bond-templates/'.$secret);
  }

}
