<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Zomato & Swiggy Order Import
    |--------------------------------------------------------------------------
    */

    'zomato' => [
        'api_key' => env('ZOMATO_API_KEY'),
        'base_url' => env('ZOMATO_BASE_URL', 'https://developers.zomato.com/api/v2.1'),
    ],

    'swiggy' => [
        'api_key' => env('SWIGGY_API_KEY'),
        'base_url' => env('SWIGGY_BASE_URL', 'https://api.swiggy.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | E-Way Bill
    |--------------------------------------------------------------------------
    */

    'eway_bill' => [
        'enabled' => env('EWAY_BILL_ENABLED', false),
        'username' => env('EWAY_BILL_USERNAME'),
        'password' => env('EWAY_BILL_PASSWORD'),
        'gstin' => env('EWAY_BILL_GSTIN'),
    ],

];
