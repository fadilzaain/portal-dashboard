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

];
