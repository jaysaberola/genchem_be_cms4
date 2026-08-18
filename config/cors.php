<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_values(array_filter(array_unique([
        'http://127.0.0.1:3000',
        'http://localhost:3000',
        'http://127.0.0.1:3002',
        'http://localhost:3002',
        env('FRONTEND_URL'),
        env('NEXT_PUBLIC_FRONTEND_URL'),
    ]))),
    'allowed_origins_patterns' => [
        '#^https://.*\.vercel\.app$#',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];