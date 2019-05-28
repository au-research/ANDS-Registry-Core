<?php
return [
    'default' => [
        'hostname' => env('DB_HOSTNAME', 'localhost'),
        'username' => env('DB_USERNAME', 'webuser'),
        'password' => env('DB_PASSWORD', ''),
        'port' => env('DB_PORT', 3306),
        'dbdriver' => 'mysqli',
        'database' => env('DB_DATABASE', 'dbs_registry')
    ],
    'registry' => [
        'database' => env('DB_DATABASE_REGISTRY', 'dbs_registry')
    ],
    'roles' => [
        'database' => env('DB_DATABASE_ROLES', 'dbs_roles')
    ],
    'vocabs' => [
        'database' => env('DB_DATABASE_VOCABS', 'dbs_vocabs')
    ],
    'statistics' => [
        'database' => env('DB_DATABASE_STATISTICS', 'dbs_statistics')
    ],
    'dois' => [
        'hostname' => env('DB_DOI_HOSTNAME', env('DB_HOSTNAME', 'localhost')),
        'username' => env('DB_DOI_HOSTNAME', env('DB_USERNAME', 'webuser')),
        'password' => env('DB_DOI_PASSWORD', env('DB_PASSWORD', '')),
        'database' => env('DB_DATABASE_DOIS', 'dbs_dois')
    ],
    'portal' => [
        'database' => env('DB_DATABASE_PORTAL', 'dbs_portal')
    ],
    'pids' => [
        'database' => env('DB_DATABASE_PIDS', 'dbs_pids'),
        'username' => env('DB_PIDS_USERNAME', 'piduser'),
        'password' => env('DB_PIDS_PASSWORD', '')
    ]
];