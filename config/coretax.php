<?php

return [

    'base_url' => env('CORETAX_BASE_URL', 'https://api.coretaxdjp.pajak.go.id'),

    'certificate_path' => env('CORETAX_CERT_PATH', storage_path('app/certificates/')),

    'certificate_password' => env('CORETAX_CERT_PASSWORD'),

    'npwp_penjual' => env('CORETAX_NPWP'),

    'timeout' => (int) env('CORETAX_TIMEOUT', 30),

    'retry' => (int) env('CORETAX_RETRY', 3),

    'environment' => env('CORETAX_ENVIRONMENT', 'development'),

    'headers' => [
        'X-DJP-TIN' => env('CORETAX_NPWP'),
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ],

];
