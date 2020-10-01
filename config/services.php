<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'ses' => [
        'key' =>
            env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'companies' => [
        'base_uri' => env('COMPANY_SERVICE_BASE_URL'),
        'secret' => env('COMPANY_SERVICE_SECRET'),
    ],
    'bidbonds' => [
        'base_uri' => env('BIDBOND_SERVICE_BASE_URL'),
        'secret' => env('BIDBOND_SERVICE_SECRET'),
    ],
    'payments' => [
        'base_uri' => env('PAYMENT_SERVICE_BASE_URL'),
        'secret' => env('PAYMENT_SERVICE_SECRET'),
    ],
    'reports' => [
        'base_uri' => env('REPORT_SERVICE_BASE_URL'),
        'secret' => env('REPORT_SERVICE_SECRET'),
    ],
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL'),
    ],
    'informa' => [
        'url' => env('INFORMA_URL'),
        'key' => env('INFORMA_KEY'),
        'phone_search_active' => env('INFORMA_PHONE_SEARCH_ACTIVE'),
    ],
    'jamii' => [
        'username' => env('JAMII_USERNAME'),
        'password' => env('JAMII_PASSWORD'),
        'branch_id' => env('JAMII_BRANCH_ID'),
        'bank_id' => env('JAMII_BANK_ID'),
        'auth_count' => env('JAMII_AUTH_COUNT',3),
        'account_officer_id' => env('JAMII_ACCOUNT_OFFICER_ID'),
    ],

];

