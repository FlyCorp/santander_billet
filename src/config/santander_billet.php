<?php

return [
    'integrations' => [
        'environment' => env('SANTANDER_BILLET_ENVIRONMENT', 'sandbox'),
        'host' => env('SANTANDER_BILLET_ENVIRONMENT') === 'sandbox' ? 'https://trust-sandbox.api.santander.com.br' : 'https://trust-open.api.santander.com.br',
        'client_id' => env('SANTANDER_BILLET_CLIENT_ID'),
        'client_secret' => env('SANTANDER_BILLET_CLIENT_SECRET'),
        'certificate_auth' => env('SANTANDER_BILLET_CERTIFICATE_AUTH'),
        'certificate_path' => env('SANTANDER_BILLET_CERTIFICATE_PATH')
    ]
];
