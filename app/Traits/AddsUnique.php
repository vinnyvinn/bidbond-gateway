<?php

namespace App\Traits;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;


trait AddsUnique
{
  public static function bootAddsUnique()
  {
    static::creating(function ($model) {
      $model->saveSecret($model);
    });
  }

  protected function saveSecret($model)
  {
   
//    $model->account_number = $this->generateAccount($model);

    $model->user_unique_id = $this->generateUnique($model);
    
  }

  protected function generateUnique($model)
  {
    $secret = uniqid();

    if ($model->all()->pluck('user_unique_id')->contains($secret)) {
      return $this->generate();
    }

    return $secret;
  }

  protected function generateAccount($model)
  {
    $secret = strtoupper(Str::random(6));

    if ($model->all()->pluck('account_number')->contains($secret)) {
      return $this->generate();
    }

    return $secret;
  }
}










 
 
 
 
 
 
 
 

