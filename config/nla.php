<?php

return [

    // NLA SRW API URL
    'api' => [
        'url' =>  env('NLA_API_URL', 'http://www.nla.gov.au/apps/srw/search/peopleaustralia')
    ],

    // NLA party prefix
    // used for detecting NLA identifiers
    'party' => [
        'prefix' => env('NLA_PARTY_PREFIX', 'http://nla.gov.au/nla.party-')
    ],

    // support for automatic data source creation and detection
    'datasource' => [
        'key' => env('NLA_DS_KEY', 'NLA_PARTY'),
        'title' => env('NLA_DS_TITLE', 'NLA Pullback Parties DS')
    ]
    
];