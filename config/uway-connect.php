<?php

return [
    'base_url' => env('UWAY_AUTH_BASE_URL', 'https://auth.uway.com.br'),
    'client_id' => env('UWAY_AUTH_CLIENT_ID'),
    'client_secret' => env('UWAY_AUTH_CLIENT_SECRET'),
    'redirect_uri' => env('UWAY_AUTH_REDIRECT_URI'),
    'scopes' => array_filter(explode(' ', (string) env('UWAY_AUTH_SCOPES', 'basic openid'))),
    'timeout' => (int) env('UWAY_AUTH_TIMEOUT', 15),
    'verify_ssl' => env('UWAY_AUTH_VERIFY_SSL', true),
];

