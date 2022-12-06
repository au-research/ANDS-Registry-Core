<?php

use ANDS\Util\Config;

return [
    'default' => 'redis',

    'connections' => [

        'redis' => [
            'driver' => 'redis',
            'name' => 'ardc:rda-registry:default',
            'url' => env('REDIS_URL', 'redis://localhost:6379')
        ],
        'redis-supernode-queue' => [
            'driver' => 'redis',
            'name' => 'ardc:rda-registry:supernode-queue',
            'url' => env('REDIS_URL', 'redis://localhost:6379')
        ]

    ]
];
