<?php

return [
    'service_url' => env('ORCID_SERVICE_URL', 'https://orcid.org/'),
    'api_url' => env('ORCID_API_URL', 'https://sandbox.orcid.org/v2.0/'),
    'public_api_url' => env('ORCID_PUBLIC_API_URL', 'https://sandbox.orcid.org/v2.0/'),
    'client_id' => env('ORCID_CLIENT_ID', ''),
    'client_secret' => env('ORCID_CLIENT_SECRET', '')
];