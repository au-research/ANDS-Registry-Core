<?php

return [
    'protocol' => env('PROTOCOL', 'http://'),
    'default_base_url' => env('PROTOCOL', 'http://') . env("BASE_URL", "localhost").'/',
    'solr_url' => env('SOLR_URL', 'http://localhost:8983/solr/'),
    'elasticsearch_url' => env('ELASTICSEARCH_URL', 'http://localhost:9200/'),
    'socket_url' => env('SOCKET_URL', 'https://localhost:3001/'),

    'api_whitelist_ip' => env('API_WHITELIST_IP', ''),
    'timezone' => env("TIMEZONE", 'Australia/Canberra'),

    'storage' => [
        'test' => [
            'driver' => 'file',
            'path' => 'tests/resources'
        ]
    ],

    'cache' => [
        'enabled' => !!env("CACHE_ENABLED", 0),

        'default' => 'file',

        'storage' => [
            'file' => [
                'namespace' => env('CACHE_FILE_NS', 'ands'),
                'path' => env('CACHE_FILE_PATH', 'engine/cache/storage'),
                'ttl' => env('CACHE_FILE_TTL', 60)
            ]
        ]
    ]
];