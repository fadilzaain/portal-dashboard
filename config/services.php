<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'googlesheet' => [
        'url'       => env('GOOGLESHEET_API_URL'),
        'key'       => env('GOOGLESHEET_API_KEY'),
        'cache_ttl' => env('GOOGLESHEET_CACHE_TTL', 300),
    ],

        'bor_api' => [
    'url' => env('BOR_API_URL', 'http://192.168.10.8:8082'),
    ],

    //Detail TT
    'infott_api' => [
    'url' => env('INFOTT_API_URL', 'http://192.168.10.29/wslokal/kominfo/realtime/infott'),
    ],

    // API monitoring SDM per jenis (new-sikawan) 
    'sdm_perjenis' => [
        'url'         => env('API_SDM_PERJENIS_URL', ''),
        'timeout'     => env('API_SDM_PERJENIS_TIMEOUT', 15),
        'cache_ttl'   => env('API_SDM_PERJENIS_CACHE_TTL', 3600),
        'verify_ssl'  => env('API_SDM_PERJENIS_VERIFY_SSL', true),
    ],

    // API bezetting SDM 
    'bezetting' => [
        'url'       => env('API_BEZETTING_URL', ''),
        'timeout'   => env('API_BEZETTING_TIMEOUT', 15),
        'cache_ttl' => env('API_BEZETTING_CACHE_TTL', 3600),
    ],

    // API SI KAWAN (internal) 
    'sikawan' => [
        'base_url' => env('API_SIKAWAN_BASE', 'http://192.168.10.8/sikawan-api/public/api/v1'),
        'timeout'  => env('API_SIKAWAN_TIMEOUT', 10),
    ],

];

