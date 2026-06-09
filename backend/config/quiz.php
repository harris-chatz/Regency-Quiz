<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Redemption Code
    |--------------------------------------------------------------------------
    |
    | "mode" can be:
    |   - "generic": all leads receive the same code ("generic_code" value)
    |   - "unique":  every lead gets a unique random code
    |
    */

    'redemption' => [
        'mode' => env('QUIZ_REDEMPTION_CODE_MODE', 'generic'),
        'generic_code' => env('QUIZ_GENERIC_REDEMPTION_CODE', 'RCMP2026'),
        'unique_prefix' => env('QUIZ_UNIQUE_CODE_PREFIX', 'RCMP-'),
        'unique_length' => 8,
        'url' => env('QUIZ_REDEMPTION_URL', 'https://www.regencycasino.gr/mont-parnes/'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Apifon — IM Gateway REST API
    |--------------------------------------------------------------------------
    |
    | When "enabled" is false the SMS is NOT actually sent to Apifon;
    | instead, a dry-run row is written to sms_logs. Use this in dev/staging.
    |
    | Docs: https://docs.apifon.com/
    |
    */

    'apifon' => [
        'enabled'       => env('APIFON_ENABLED', false),
        'base_url'      => env('APIFON_BASE_URL', 'https://ars.apifon.com'),
        'endpoint'      => env('APIFON_ENDPOINT', '/services/api/v1/im/send'),
        'oauth_token'   => env('APIFON_OAUTH_TOKEN'),
        'sender_id'     => env('APIFON_SENDER_ID', 'MONT PARNES'),
        'sms_template'  => env(
            'APIFON_SMS_TEMPLATE',
            'Συγχαρητήρια! Ο κωδικός εξαργύρωσής σου είναι {code}. Δες περισσότερα: {url}'
        ),
        'country_code'  => env('APIFON_COUNTRY_CODE', '30'),
        'timeout'       => (int) env('APIFON_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin (CSV Export)
    |--------------------------------------------------------------------------
    */

    'admin' => [
        'username' => env('ADMIN_USERNAME'),
        'password' => env('ADMIN_PASSWORD'),
    ],

];
