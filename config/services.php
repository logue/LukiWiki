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

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model'   => App\User::class,
        'key'     => env('STRIPE_KEY'),
        'secret'  => env('STRIPE_SECRET'),
        'webhook' => [
            'secret'    => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    // SNS Authentication

    // https://developer.twitter.com/apps
    'twitter' => [
        'client_id'     => env('TWITTER_ID'),
        'client_secret' => env('TWITTER_SECRET'),
        'redirect'      => env('APP_URL').'/:login/twitter/callback',
    ],

    // https://developers.facebook.com/
    'facebook' => [
        'client_id'     => env('FACEBOOK_ID'),
        'client_secret' => env('FACEBOOK_SECRET'),
        'redirect'      => env('APP_URL').'/:login/facebook/callback',
    ],

    // https://console.developers.google.com/
    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('APP_URL').'/:login/google/callback',
    ],

    // https://docs.microsoft.com/en-us/azure/app-service/configure-authentication-provider-microsoft
    'live' => [
        'client_id'     => env('LIVE_KEY'),
        'client_secret' => env('LIVE_SECRET'),
        'redirect'      => env('APP_URL').'/:login/live/callback',
    ],

    // https://developers.line.biz/ja/
    'line' => [
        'client_id'     => env('LINE_CLIENT_ID'),
        'client_secret' => env('LINE_CLIENT_SECRET'),
        'redirect'      => env('APP_URL').'/:login/line/callback',
    ],

    // https://github.com/settings/applications/new
    'github' => [
        'client_id'     => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect'      => env('APP_URL').'/:login/github/callback',
    ],
];
