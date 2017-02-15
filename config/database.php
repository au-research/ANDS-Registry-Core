<?php
return [
    'default' => [
        'hostname' => env('DB_HOSTNAME', 'localhost'),
        'username' => env('DB_USERNAME', 'webuser'),
        'password' => env('DB_PASSWORD', ''),
        'dbdriver' => 'mysqli'
    ],
    'registry' => [
        'database' => 'dbs_registry'
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
        'database' => 'dbs_dois'
    ],
    'portal' => [
        'database' => 'dbs_portal'
    ]
];