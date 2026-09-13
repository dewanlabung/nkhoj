<?php

return [
    'omniroute' => [
        'endpoint'      => env('OMNIROUTE_ENDPOINT', 'http://localhost:20128/v1'),
        'key'           => env('OMNIROUTE_KEY', 'sk-omniroute-local'),
        'primary_model' => env('OMNIROUTE_PRIMARY_MODEL', 'claude-opus-5'),
        'fast_model'    => env('OMNIROUTE_FAST_MODEL', 'gemini-2.5-flash'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'),
    ],

    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => env('FACEBOOK_REDIRECT_URI'),
    ],

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme'   => 'https',
    ],

    'fcm' => [
        // Legacy key removed (FCM legacy HTTP API shut down June 2024).
        // Use FCM HTTP v1 with a service account JSON file.
        'project_id'           => env('FIREBASE_PROJECT_ID'),
        'service_account_path' => env('FIREBASE_SERVICE_ACCOUNT_PATH'),
    ],
];
