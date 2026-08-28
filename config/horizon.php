<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Horizon Domain
    |--------------------------------------------------------------------------
    |
    | This is the subdomain where Horizon will be accessible from. If this
    | setting is null, Horizon will reside under the same domain as the
    | application. Otherwise, this value will be used as the subdomain.
    |
    */

    'domain' => env('HORIZON_DOMAIN', null),

    /*
    |--------------------------------------------------------------------------
    | Horizon Path
    |--------------------------------------------------------------------------
    |
    | This is the path where Horizon will be accessible from. Feel free to
    | change this path to anything you like. Note that the URI will not
    | affect the path of the API that are proxied to this back-end.
    |
    */

    'path' => env('HORIZON_PATH', 'horizon'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Connection
    |--------------------------------------------------------------------------
    |
    | This is the name of the Redis connection that Horizon will use to
    | store the data required to run your queues and monitor them. It
    | includes the processed, failed, and other job information.
    |
    | Note: For database queue connection, this still uses Redis for
    | Horizon's metrics. If Redis is not available, use 'database'.
    |
    */

    'use' => env('HORIZON_REDIS_CONNECTION', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Environment
    |--------------------------------------------------------------------------
    |
    | This controls the "environment" mode that Horizon will run in. The
    | environment controls the ways in which Horizon displays data in
    | the UI. When this is set to "production" some visual tweaks will
    | be made to enhance the experience for larger production setups.
    |
    */

    'environment' => env('HORIZON_ENVIRONMENT', env('APP_ENV', 'production')),

    /*
    |--------------------------------------------------------------------------
    | Horizon Prefix
    |--------------------------------------------------------------------------
    |
    | This prefix will be used when storing all Horizon data in Redis. You
    | may modify the prefix when you have multiple installations to avoid
    | collisions with other Horizon installations on the same server.
    |
    */

    'prefix' => env('HORIZON_PREFIX', 'horizon:'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to every Horizon route, giving you
    | the chance to add your own middleware to this list or change any of
    | the existing middleware. Typically, you will want to add your own
    | middleware that verifies the user is logged-in and has access.
    |
    */

    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Horizon Job Trim Times
    |--------------------------------------------------------------------------
    |
    | Here you can configure for how long (in hours) Horizon will persist
    | the historical job data in your Redis instance. By default Horizon
    | will retain up to 7 days of job data. Any job data older than this
    | will get pruned from the database.
    |
    */

    'trim' => [
        'recent' => 60,
        'pending' => 60,
        'completed' => 24 * 7,
        'recent_failed' => 24,
        'failed' => 24 * 7,
        'monitored' => 24 * 7,
    ],

    /*
    |--------------------------------------------------------------------------
    | Fast Termination
    |--------------------------------------------------------------------------
    |
    | When this option is enabled, Horizon's "terminate" command will not
    | wait on all of the workers to terminate. Instead, it will kill the
    | master process so it "short cuts" all of this waiting.
    |
    */

    'fast_termination' => false,

    /*
    |--------------------------------------------------------------------------
    | Queue Worker Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define the queue worker settings used by Horizon. These
    | workers can have different configurations each. Horizon will boot as
    | many workers as needed based on these configurations and the scale
    | factor provided in the environment variables.
    |
    */

    'workers' => [
        [
            'name' => 'default',
            'connection' => 'database',
            'queue' => ['default'],
            'balance' => 'simple',
            'processes' => env('HORIZON_PROCESSES', 1),
            'tries' => 3,
            'timeout' => 60,
            'sleep' => 3,
        ],
        [
            'name' => 'notifications',
            'connection' => 'database',
            'queue' => ['notifications', 'emails'],
            'balance' => 'simple',
            'processes' => env('HORIZON_NOTIFICATION_PROCESSES', 2),
            'tries' => 3,
            'timeout' => 120,
            'sleep' => 3,
        ],
        [
            'name' => 'reports',
            'connection' => 'database',
            'queue' => ['reports'],
            'balance' => 'simple',
            'processes' => env('HORIZON_REPORT_PROCESSES', 1),
            'tries' => 2,
            'timeout' => 300,
            'sleep' => 5,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Horizon Metrics
    |--------------------------------------------------------------------------
    |
    | Here you may configure how many snapshots of queue metrics that
    | Horizon will keep in Redis. By default, it will keep the last
    | 7 days of metrics. You may change this configuration here.
    |
    */

    'metrics' => [
        'trim_snapshots' => [
            'job_10_minutes' => 60 * 24 * 7,
            'job_60_minutes' => 60 * 24 * 7,
            'job_all_time' => 0,
            'wait_15_minutes' => 60 * 24 * 7,
            'wait_60_minutes' => 60 * 24 * 7,
            'wait_all_time' => 0,
            'throughput_15_minutes' => 60 * 24 * 7,
            'throughput_60_minutes' => 60 * 24 * 7,
            'throughput_all_time' => 0,
            'time_on_queue_15_minutes' => 60 * 24 * 7,
            'time_on_queue_60_minutes' => 60 * 24 * 7,
            'time_on_queue_all_time' => 0,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Tags
    |--------------------------------------------------------------------------
    |
    | You may wish to tag your jobs by certain metadata. Horizon allows you
    | to define any tags that resonate with your application by specifying
    | these options below. The tags listed here will automatically display
    | in the Horizon UI and be available for filtering in the monitoring.
    |
    */

    'tags' => [
        // 'App\Jobs\ExampleJob@handle',
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-scaling
    |--------------------------------------------------------------------------
    |
    | Here you may configure auto-scaling settings for your application. The
    | auto-scaling workers will scale according to these thresholds.
    |
    */

    'auto_scaling' => [
        'enabled' => env('HORIZON_AUTO_SCALING', false),
        'min_processes' => 1,
        'max_processes' => 4,
        'balancing' => [
            'auto' => 60,
        ],
    ],
];
