<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app/private'),
            'serve'  => true,
            'throw'  => false,
            'url'    => env('APP_URL') . '/storage',
        ],
        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw'      => false,
        ],
        'r2' => [
            'driver'   => 's3',
            'key'      => env('CLOUDFLARE_R2_ACCESS_KEY_ID'),
            'secret'   => env('CLOUDFLARE_R2_SECRET_ACCESS_KEY'),
            'region'   => 'auto',
            'bucket'   => env('CLOUDFLARE_R2_BUCKET'),
            'url'      => env('CLOUDFLARE_R2_URL'),
            'endpoint' => env('CLOUDFLARE_R2_ENDPOINT'),
            'use_path_style_endpoint' => true,
            'visibility' => 'public',
        ],
    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
