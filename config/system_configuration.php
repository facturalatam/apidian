<?php

return [
    'allow_public_download' => env('ALLOW_PUBLIC_DOWNLOAD', true),
    'force_https' => env('FORCE_HTTPS', false),
    'validate_before_sending' => env('VALIDATE_BEFORE_SENDING',false),
    'apply_send_customer_credentials' => env('APPLY_SEND_CUSTOMER_CREDENTIALS', TRUE),
    'save_response_dian_to_db' => env('SAVE_RESPONSE_DIAN_TO_DB', FALSE),
    'enable_api_register' => env('ENABLE_API_REGISTER', TRUE),
    'url_api_cert_modernizer' => env('URL_API_CERT_MODERNIZER', null),
    'pdftotext_path' => env('PDFTOTEXT_PATH', null),
];
