<?php

use App\Mail\CompanyActivate;

Route::get('bond', 'TestController@viewBidbondPdfResponse');

Route::get('mail',function(){
    $user = App\User::first();
    return new CompanyActivate($user,"code");
});
