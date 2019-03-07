<?php

return [
    'release_version' => env('VERSION', '30'),
    'environment_name' => env("ENVIRONMENT_NAME", "RDA"),
    'environment_colour' => env("ENVIRONMENT_COLOUR" , "#6EBF21"),
    'environment_logo' => '/img/ands_logo_white.png',
    'protocol' => env('PROTOCOL', 'http://'),
    'default_base_url' => env('PROTOCOL', 'http://') . env("BASE_URL", "localhost").'/',
    'subject_vocab_proxy' => env('PROTOCOL', 'http://') . env("BASE_URL", "localhost").'/apps/vocab_widget/proxy/',
    'solr_url' => env('SOLR_URL', 'http://localhost:8983/solr/'),
    'elasticsearch_url' => env('ELASTICSEARCH_URL', 'http://localhost:9200/'),
    'socket_url' => env('SOCKET_URL', 'http://localhost:3001/'),
    'deployment_state' => env('ENVIRONMENT', "development"),
    'api_whitelist_ip' => env('API_WHITELIST_IP', ''),
    'timezone' => env("TIMEZONE", 'Australia/Canberra'),
    'site_admin' => env('ADMIN_NAME', null),
    'site_admin_email' => env('ADMIN_EMAIL', null),
    'google_api_key' => env('GOOGLE_API_KEY', null),
    'shibboleth_sp' => false,
    'services_registry_url' => env('SERVICES_DISCOVERY_SERVICE_URL', null),
    'harvested_contents_path' => env('HARVESTED_CONTENTS', '/var/harvested_contents/'),
    'rda_urchin_id' => env('RDA_URCHIN_ID', ''),
    'svc_urchin_id' => env('SVC_URCHIN_ID', ''),
    'storage' => [
        'test' => [
            'driver' => 'file',
            'path' => 'tests/resources'
        ],
        'schema' => [
            'driver' => 'file',
            'path' => 'etc/schema'
        ],
        'logs' => [
            'path' => env('LOGS_PATH', 'logs'),

            // legacy_path for CodeIgniter, default empty for engine/logs location
            'legacy_path' => env('LOGS_PATH_LEGACY', '')
        ],
        'uploads' => [
            'path' => env('UPLOADS_PATH', 'assets/uploads')
        ]
    ],

    'harvester' => [
        'url' => env('HARVESTER_URL', 'http://localhost:7020')
    ],

    'taskmanager' => [
        'url' => env('TASKMANAGER_URL', 'http://localhost:7021')
    ],

    'cache' => [
        'enabled' => !!env("CACHE_ENABLED", 0),

        'default' => 'file',

        'drivers' => [
            'file' => [
                'namespace' => env('CACHE_FILE_NS', 'ands'),
                'path' => env('CACHE_FILE_PATH', 'engine/cache/storage'),
                'ttl' => env('CACHE_FILE_TTL', 60)
            ]
        ],

        'store' => [
            'file' => [
                'driver' => 'file',
                'namespace' => 'ands'
            ],
            'graph' => [
                'driver' => 'file',
                'namespace' => 'graph',
            ],
            'suggestions' => [
                'driver' => 'file',
                'namespace' => 'suggestions'
            ]
        ]
    ],
    'tracking' =>
    [
        'googleGA' => [
            'enabled' => false,
            'keys' => ['id' => '']
            ],
        'luckyOrange' => [
            'enabled' => false,
            'keys' =>['id' => '']

    ]
    ],
    'enabled_modules' => [

        // Default modules (simply installs the registry, access control and portal)
        'roles',
        'registry',
        'portal',

        //  - 	Identifier service modules
        			'mydois',
        			'pids',

        // 	- 	Content Management System modules
        			'theme_cms',
        			'bulk_tag',
        			'cms',

        //	-	Statistics dashboard
        			'statistics',

        //  -   Twitter announcement app
        			'twitter',

        // These modules require: ANDS-Developer-Portal to be installed:
        // 	- 	Developer API documentation
        			'toolbox',

    ],





    'cookie_domain' => '.ands.org.au'
];