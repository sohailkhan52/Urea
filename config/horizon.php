<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Horizon Domain
    |--------------------------------------------------------------------------
    |
    | This is the subdomain where Horizon will be accessible from. If the
    | domain is set to null, Horizon will reside under the same domain
    | as the application. Otherwise, this value will be used as the domain.
    |
    */
    'domain' => env('HORIZON_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Horizon will be accessible from. Feel free
    | to change this path to anything you like. Note that the URI will not
    | affect the path of the API that are proxied to this application.
    |
    */
    'path' => env('HORIZON_PATH', 'horizon'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Connection
    |--------------------------------------------------------------------------
    |
    | This is the name of the Redis connection that Horizon will use to
    | communicate with Redis. It will retrieve the connection config from
    | the config/database.php file.
    |
    */
    'use' => 'redis',

    /*
    |--------------------------------------------------------------------------
    | Horizon Environment
    |--------------------------------------------------------------------------
    |
    | The environment that Horizon is running in can be controlled via the
    | environment variable HORIZON_ENVIRONMENT. By default, it is set to
    | production which will hide certain metrics from display.
    |
    */
    'environment' => env('HORIZON_ENVIRONMENT', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Prefix
    |--------------------------------------------------------------------------
    |
    | This prefix will be used when storing Horizon data in Redis. You may
    | modify the prefix when you are hosting multiple Horizon instances
    | to avoid collisions with other Horizon instances on your server.
    |
    */
    'prefix' => env('HORIZON_PREFIX', 'horizon:'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Purge
    |--------------------------------------------------------------------------
    |
    | Here you may configure how much data Horizon should retain by default
    | while it executes. By specifying a lower number, you will require
    | fewer resources while running the application. By default, retain
    | the data for 48 hours.
    |
    */
    'trim' => [
        'realtime' => 120,
        'recent' => 60 * 60,
        'failed' => 10080 * 60,
        'monitored' => 10080 * 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Fast Termination
    |--------------------------------------------------------------------------
    |
    | When this option is enabled, Horizon will automatically write the
    | latest data to storage before performing a graceful termination
    | of the application. This helps prevent data loss on deployments.
    |
    */
    'fast_termination' => false,

    /*
    |--------------------------------------------------------------------------
    | Supervisors
    |--------------------------------------------------------------------------
    |
    | The supervisors below "watch" your queues and will restart any new
    | workers that have died so that workers will always stay running. If
    | you are self-hosting this application, make sure that this is set
    | to run as a daemon.
    |
    */
    'supervisors' => [
        [
            'name' => 'default',
            'connection' => 'redis',
            'queue' => ['default', 'payments', 'notifications'],
            'balance' => 'simple',
            'autoScalingStrategy' => 'time',
            'minProcesses' => env('HORIZON_PROCESSES', 1),
            'maxProcesses' => env('HORIZON_PROCESSES', 1),
            'balanceMaxShift' => '1',
            'balanceCooldown' => 3,
            'tries' => 3,
            'timeout' => 120,
            'sleep' => 3,
            'maxSleepSeconds' => 10,
        ],
        [
            'name' => 'notifications',
            'connection' => 'redis',
            'queue' => ['notifications'],
            'balance' => 'simple',
            'processes' => env('HORIZON_NOTIFICATION_PROCESSES', 2),
            'tries' => 3,
            'timeout' => 300,
            'sleep' => 3,
        ],
        [
            'name' => 'reports',
            'connection' => 'redis',
            'queue' => ['reports'],
            'balance' => 'simple',
            'processes' => env('HORIZON_REPORT_PROCESSES', 1),
            'tries' => 1,
            'timeout' => 600,
            'sleep' => 5,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Metrics
    |--------------------------------------------------------------------------
    |
    | Horizon exposes various metrics that may be useful for monitoring
    | the health and performance of the queue system. They may be
    | viewed within the Horizon dashboard.
    |
    */
    'metrics' => [
        'redis' => [
            'connection' => env('HORIZON_REDIS_CONNECTION', 'redis'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Options
    |--------------------------------------------------------------------------
    |
    | The Redis options that should be used when connecting to Redis.
    |
    */
    'options' => [
        'retry' => 3,
        'timeout' => 30,
    ],
];
