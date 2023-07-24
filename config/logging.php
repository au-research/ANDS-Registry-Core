<?php

return [

    // default log channel
    'default' => env('LOG_CHANNEL', 'registry'),

    'channels' => [

        'registry' => [
            'driver' => 'single',
            'path' => env('LOGS_PATH', '/var/log/registry/'),
            'file' => 'registry.app.log',
            'level' => env('LOG_LEVEL', 'debug')
        ],

        'portal' => [
            'driver' => 'daily',
            'path' => env('LOGS_PATH', '/var/log/registry/'),
            'file' => 'portal.app.log',
            'days' => 14,
            'level' => env('LOG_LEVEL', 'debug'),
        ]

    ]

];