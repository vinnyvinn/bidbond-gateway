<?php

return [
    'username' => env('AFT_USERNAME'),
    'apikey' => env('AFT_APIKEY'),
    'enable_sms' => env('AFT_ENABLE_SMS',false),
    'appKey'=>env('JBB_APP_KEY'),//'8a1e8099992848e7bf93cbcb96d',
    'appSecret'=>env('JBB_APP_SECRET'),//'b00188518646a3d4c4302be4ca19eaa1f7b33',
    'profileId'=>env('JBB_PROFILE'),//1807132,
    'AppLink'=>env('JBB_APP_URL'),//'https://api.ujumbe.co.ke/v2/sendSms'
];

