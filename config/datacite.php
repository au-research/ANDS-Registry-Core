<?php

$prefixes = explode(', ', env('DATACITE_PREFIXES', ''));

return [
    'base_url' => env('DATACITE_URL', 'https://mds.datacite.org/'),
    'name_prefix' => env('DATACITE_NAME', 'ANDS'),
    'name_middle' => env('DATACITE_NAME_MIDDLE', 'CENTRE'),
    'password' =>  env('DATACITE_PASSWORD', null),
    'response_success' => 'OK',
    'prefixs' => $prefixes
];