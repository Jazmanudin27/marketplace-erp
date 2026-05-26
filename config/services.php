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

    'shopee' => [

        'host' => env('SHOPEE_HOST'),

        'partner_id' => env('SHOPEE_PARTNER_ID'),

        'partner_key' => env('SHOPEE_PARTNER_KEY'),

        'redirect_url' => env('SHOPEE_REDIRECT_URL'),
    ],

    'tokopedia' => [

        'client_id' => env('TOKOPEDIA_CLIENT_ID'),

        'client_secret' => env('TOKOPEDIA_CLIENT_SECRET'),

        'redirect_url' => env('TOKOPEDIA_REDIRECT_URL'),
    ],

    'tiktok' => [
        'app_key' => env('TIKTOK_APP_KEY'),
        'app_secret' => env('TIKTOK_APP_SECRET'),
        'redirect_url' => env('TIKTOK_REDIRECT_URL'),
    ],

    'lazada' => [

        'client_id' => env('LAZADA_CLIENT_ID'),

        'client_secret' => env('LAZADA_CLIENT_SECRET'),

        'redirect_url' => env('LAZADA_REDIRECT_URL'),

        'region' => env('LAZADA_REGION', 'id'),
    ],

];
