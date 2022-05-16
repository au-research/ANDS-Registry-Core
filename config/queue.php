<?php

use ANDS\Util\Config;

return [
    'default' => 'redis',

    'connections' => [

        'redis' => [
            'driver' => 'redis',
            'name' => 'ardc:rda-registry:default',
            'url' => env('REDIS_URL', 'redis://localhost:6379')
        ]

    ]
];
