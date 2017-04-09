<?php
return [
    'default' => [
        'hostname' => env('DB_HOSTNAME', 'localhost'),
        'username' => env('DB_USERNAME', 'webuser'),
        'password' => env('DB_PASSWORD', ''),
        'dbdriver' => 'mysqli'
    ],
    'registry' => [
        'database' => env('DB_DATABASE', 'dbs_registry')
    ],
    'roles' => [
        'database' => 'dbs_roles'
    ],
    'vocabs' => [
        'database' => 'dbs_vocabs'
    ],
    'statistics' => [
        'database' => 'dbs_statistics'
    ],
    'dois' => [
        'hostname' => env('DB_DOI_HOSTNAME', env('DB_HOSTNAME', 'localhost')),
        'username' => env('DB_DOI_HOSTNAME', env('DB_USERNAME', 'webuser')),
        'password' => env('DB_DOI_PASSWORD', env('DB_PASSWORD', '')),
        'database' => 'dbs_dois'
    ],
    'portal' => [
        'database' => 'dbs_portal'
    ],
    'pids' => [
        'database' => 'dbs_pids'
    ]
];