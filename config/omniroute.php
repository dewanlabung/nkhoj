<?php

return [
    'endpoint'      => env('OMNIROUTE_ENDPOINT', 'http://localhost:20128/v1'),
    'key'           => env('OMNIROUTE_KEY', 'sk-omniroute-local'),
    'primary_model' => env('OMNIROUTE_PRIMARY_MODEL', 'claude-opus-5'),
    'fast_model'    => env('OMNIROUTE_FAST_MODEL', 'gemini-2.5-flash'),
];
