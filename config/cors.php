<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS
    |--------------------------------------------------------------------------
    |
    | allowedOrigins, allowedHeaders and allowedMethods can be set to array('*')
    | to accept any value.
    |
    */
   
    // 'supportsCredentials' => false,
    // 'allowedOrigins' => ['*'],
    // 'allowedOriginsPatterns' => [],
    // 'allowedHeaders' => ['*'],
    // 'allowedMethods' => ['*'],
    // 'exposedHeaders' => [],
    // 'maxAge' => 0,
    
    'paths' => ['api/*', 'sanctum/csrf-cookie', "**/print/*", ], // Se agrego print/ para mostrarse el format pdf en vendeya

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
