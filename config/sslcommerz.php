<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SSLCommerz Credentials & Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for SSLCommerz payment gateway integration in LeftoverLink.
    |
    */

    'store_id' => env('SSLCOMMERZ_STORE_ID', 'testbox'),
    
    'store_password' => env('SSLCOMMERZ_STORE_PASSWORD', 'qwerty'),

    'sandbox' => env('SSLCOMMERZ_SANDBOX', true),

    'api_domain' => env('SSLCOMMERZ_SANDBOX', true) 
        ? 'https://sandbox.sslcommerz.com' 
        : 'https://securepay.sslcommerz.com',

    'initiate_endpoint' => '/gwprocess/v4/api.php',

    'validation_endpoint' => '/validator/api/validationserverAPI.php',

    'currency' => env('SSLCOMMERZ_CURRENCY', 'BDT'),
];
