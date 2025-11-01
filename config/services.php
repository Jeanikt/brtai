<?php

return [
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        'guzzle' => [
            'verify' => env('GUZZLE_VERIFY', true),
        ],
    ],
    'abacatepay' => [
        'key' => env('ABACATEPAY_API_KEY', 'abc_dev_2zd5G3HnxkPTxcPs3qd56Kkz'),
        'url' => env('ABACATEPAY_BASE_URL', 'https://api.abacatepay.com/v1'),
        'webhook_secret' => env('ABACATEPAY_WEBHOOK_SECRET', 'webh_dev_24Kzwr0q6YCHGzkDUeGAQ6JL'),
    ],
    'discord' => [
        'webhook' => env('DISCORD_WEBHOOK_URL'),
    ],
];
