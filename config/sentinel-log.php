<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Global Toggle
    |--------------------------------------------------------------------------
    */
    'enabled'    => env('SENTINEL_LOG_ENABLED', true),
    'table_name' => 'authentication_logs',

    /*
    |--------------------------------------------------------------------------
    | Tracked Events
    |--------------------------------------------------------------------------
    */
    'events' => [
        'login'  => true,
        'logout' => true,
        'failed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Pruning
    |--------------------------------------------------------------------------
    */
    'prune' => [
        'enabled' => true,
        'days'    => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'new_device' => [
            'enabled'   => env('SENTINEL_LOG_NOTIFY_NEW_DEVICE', true),
            'channels'  => ['mail'],
            'threshold' => 1,
        ],
        'failed_attempt' => [
            'enabled'   => env('SENTINEL_LOG_NOTIFY_FAILED_ATTEMPT', true),
            'channels'  => ['mail'],
            'threshold' => 3,
            'window'    => 60,
        ],
        'session_hijacking' => [
            'enabled'  => env('SENTINEL_LOG_NOTIFY_HIJACKING', true),
            'channels' => ['mail'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication
    |--------------------------------------------------------------------------
    | required    — force every TwoFactorAuthenticatable user to set up 2FA.
    | setup_route — named route the middleware redirects to when 2FA is not yet
    |               configured for the user.
    | verify_route — named route for the TOTP challenge after each login.
    |                The middleware redirects here when 2FA is set up but the
    |                '2fa_verified' session key is absent.
    |--------------------------------------------------------------------------
    */
    'two_factor' => [
        'enabled'      => env('SENTINEL_LOG_2FA_ENABLED', false),
        'required'     => env('SENTINEL_LOG_2FA_REQUIRED', false),
        'middleware'   => 'sentinel-log.2fa',
        'setup_route'  => 'two-factor.setup',
        'verify_route' => 'two-factor.verify',
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Management
    |--------------------------------------------------------------------------
    */
    'sessions' => [
        'enabled'    => env('SENTINEL_LOG_SESSIONS_ENABLED', true),
        'max_active' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Brute-Force Protection
    |--------------------------------------------------------------------------
    */
    'brute_force' => [
        'enabled'        => env('SENTINEL_LOG_BRUTE_FORCE_ENABLED', true),
        'threshold'      => 5,
        'window'         => 15,
        'block_duration' => 24,
    ],

    /*
    |--------------------------------------------------------------------------
    | Geolocation
    |--------------------------------------------------------------------------
    | geo_provider_url — base URL of the geolocation provider; the package
    |                    appends /{ip} automatically. Must return JSON with
    |                    keys: success, country, city, latitude, longitude, ip.
    |                    Default: ipwho.is (free, HTTPS, no API key required).
    |--------------------------------------------------------------------------
    */
    'geo_test_ip'      => env('SENTINEL_LOG_GEO_TEST_IP', null),
    'geo_provider_url' => env('SENTINEL_LOG_GEO_PROVIDER_URL', 'https://ipwho.is'),

    'geo_fencing' => [
        'enabled'           => env('SENTINEL_LOG_GEO_FENCING_ENABLED', false),
        'allowed_countries' => array_values(array_filter(
            explode(',', env('SENTINEL_LOG_GEO_FENCING_ALLOWED_COUNTRIES', 'United States,Canada'))
        )),
    ],

    /*
    |--------------------------------------------------------------------------
    | SSO
    |--------------------------------------------------------------------------
    */
    'sso' => [
        'enabled'        => env('SENTINEL_LOG_SSO_ENABLED', false),
        'client_id'      => env('SENTINEL_LOG_SSO_CLIENT_ID', 'default_client'),
        'token_lifetime' => 24,
    ],

    /*
    |--------------------------------------------------------------------------
    | Location Verification (new in v0.2.0)
    |--------------------------------------------------------------------------
    | Sends verify/deny email links when a login is detected from a new
    | location. GET links show a confirmation page; POST links act.
    | token_ttl — minutes until verify/deny links expire (default 30).
    |--------------------------------------------------------------------------
    */
    'location_verification' => [
        'enabled'               => env('SENTINEL_LOG_LOCATION_VERIFICATION_ENABLED', true),
        'channels'              => ['mail'],
        'token_ttl'             => 30,
        'redirect_after_verify' => '/',
        'redirect_after_deny'   => '/',
    ],

];
