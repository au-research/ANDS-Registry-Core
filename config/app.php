<?php

return [
    'release_version' => env('VERSION', '31'),
    'environment_name' => env("ENVIRONMENT_NAME", "RDA"),
    'environment_colour' => env("ENVIRONMENT_COLOUR" , "#6EBF21"),
    'environment_logo' => 'img/ardc_logo_white.png',
    'protocol' => env('PROTOCOL', 'http://'),
    'default_base_url' => env('PROTOCOL', 'http://') . env("BASE_URL", "localhost").'/',
    'subject_vocab_proxy' => env('PROTOCOL', 'http://') . env("BASE_URL", "localhost").'/apps/vocab_widget/proxy/',
    'solr_url' => env('SOLR_URL', 'http://localhost:8983/solr/'),
    'elasticsearch_url' => env('ELASTICSEARCH_URL', 'http://localhost:9200/'),
    'socket_url' => env('SOCKET_URL', 'http://localhost:3001/'),
    'deployment_state' => env('ENVIRONMENT', "development"),
    'cookie_domain' => env('COOKIE_DOMAIN', '.ands.org.au'),
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
    'python_bin' => env('PYTHON_BIN', '/usr/bin/python3.6'),
    'doi_link_checker_script' => env('DOI_LINK_CHECKER_SCRIPT','/opt/ands/registry/etc/misc/python/linkchecker/linkchecker.py --html_output -i /opt/ands/registry/etc/misc/python/linkchecker/linkchecker.ini -m DOI'),
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
            'path' => env('LOGS_PATH', '/var/log/'),

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
            'enabled' => env("GOOGLE_URCHIN_ID") ? true : false,
            'keys' => ['id' => env("GOOGLE_URCHIN_ID", "")]
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
    'content_providers' => [
            'http://www.isotc211.org/2005/gmd' => \ANDS\Registry\ContentProvider\ISO\ISO191153ContentProvider::class,
            'json-ld' => \ANDS\Registry\ContentProvider\JSONLD\JSONLDContentProvider::class,
            'JSONLD' => \ANDS\Registry\ContentProvider\JSONLD\JSONLDContentProvider::class,
            'JSONLDHarvester' => \ANDS\Registry\ContentProvider\JSONLD\JSONLDContentProvider::class,
            'CSWHarvester' =>  \ANDS\Registry\ContentProvider\ISO\ISO191153ContentProvider::class,
            'https://pure.bond.edu.au' =>  \ANDS\Registry\ContentProvider\PURE\BONDContentProvider::class
    ]
];