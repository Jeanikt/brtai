<?php

return [
    'default' => env('FILESYSTEM_DISK', 'supabase'),

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL', null),
            'endpoint' => env('AWS_ENDPOINT', null),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

        'supabase' => [
            'driver' => 's3',
            'key' => env('SUPABASE_S3_ACCESS_KEY'),
            'secret' => env('SUPABASE_S3_SECRET'),
            'region' => 'us-east-1',
            'bucket' => env('SUPABASE_BUCKET', 'events'),
            'url' => env('SUPABASE_URL') . '/storage/v1/object/public/' . env('SUPABASE_BUCKET', 'events'),
            'endpoint' => env('SUPABASE_ENDPOINT'),
            'use_path_style_endpoint' => true,
            'throw' => false,
            'report' => false,
            'visibility' => 'public',
            'options' => [
                'CacheControl' => 'public, max-age=31536000',
            ],
        ],
    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
