<?php

return [
    'base_url' => env('DATACITE_URL', 'https://mds.datacite.org/'),
    'name_prefix' => env('DATACITE_NAME', 'ANDS'),
    'name_middle' => env('DATACITE_NAME_MIDDLE', 'CENTRE'),
    'password' =>  env('DATACITE_PASSWORD', null),
    'contact-name' => env('DATACITE_CONTACT_NAME', 'ANDS Service'),
    'contact-email' => env('DATACITE_CONTACT_EMAIL','services@ands.org.au'),
    'fabrica' => [
        'api_url' => env('DATACITE_FABRICA_API_URL', 'https://app.datacite.org'),
        'url' => env('DATACITE_FABRICA_URL', 'https://doi.test.datacite.org'),
        'username' => env('DATACITE_FABRICA_USERNAME', 'ands'),
        'password' => env('DATACITE_FABRICA_PASSWORD', null)
    ],
    'response_success' => 'OK'
];