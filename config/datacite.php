<?php

return [
    'base_url' => env('DATACITE_URL', 'https://mds.datacite.org/'),
    'base_test_url' => env('DATACITE_TEST_URL', 'https://mds.test.datacite.org/'),
    'name_prefix' => env('DATACITE_NAME', 'ANDS'),
    'name_middle' => env('DATACITE_NAME_MIDDLE', 'CENTRE'),
    'password' =>  env('DATACITE_PASSWORD', null),
    'testPassword' =>  env('DATACITE_TEST_PASSWORD', null),
    'contact-name' => env('DATACITE_CONTACT_NAME', 'ANDS Service'),
    'contact-email' => env('DATACITE_CONTACT_EMAIL','services@ands.org.au'),
    'known_doi' =>env('DATACITE_KNOWN_DOI',null),
    'fabrica' => [
        'api_url' => env('DATACITE_FABRICA_API_URL', 'https://api.datacite.org'),
        'url' => env('DATACITE_FABRICA_URL', 'https://doi.test.datacite.org'),
        'username' => env('DATACITE_FABRICA_USERNAME', 'ands'),
        'password' => env('DATACITE_FABRICA_PASSWORD', null),
        'testPassword' => env('DATACITE_FABRICA_TEST_PASSWORD', null),
    ],
    'response_success' => 'OK'
];