<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Octane Server
    |--------------------------------------------------------------------------
    |
    | This value contains the name of the server that Octane will use when
    | it is started. By default, Octane will start using the RoadRunner
    | server. However, you are free to specify any server requested.
    |
    | Supported: "roadrunner"
    |
    */

    'server' => env('OCTANE_SERVER', 'roadrunner'),

    /*
    |--------------------------------------------------------------------------
    | Workers
    |--------------------------------------------------------------------------
    |
    | The number of workers that should be assigned to the Octane server.
    | This includes the primary worker. In general, this should equal your
    | CPU count allowing the server to handle incoming requests in parallel.
    |
    */

    'workers' => env('OCTANE_WORKERS', 4),

    /*
    |--------------------------------------------------------------------------
    | Max Requests
    |--------------------------------------------------------------------------
    |
    | The number of requests to process before reloading the worker process.
    | This is useful to ensure that memory leaks do not cause problems over
    | time. Set this value to 0 to disable worker reloads.
    |
    */

    'max_requests' => env('OCTANE_MAX_REQUESTS', 500),

    /*
    |--------------------------------------------------------------------------
    | Task Workers
    |--------------------------------------------------------------------------
    |
    | These are the number of background "workers" to spawn for processing
    | task jobs spawned by the server. These workers handle background
    | jobs and are not used to process HTTP requests.
    |
    */

    'task_workers' => env('OCTANE_TASK_WORKERS', 4),

    /*
    |--------------------------------------------------------------------------
    | Warm Cache
    |--------------------------------------------------------------------------
    |
    | Determines whether Octane should warm the cache on boot. This will call
    | each of the caches defined in your application's cache config, which
    | will warm up any necessary connections that might be required.
    |
    */

    'warm_cache' => true,

    /*
    |--------------------------------------------------------------------------
    | Watch
    |--------------------------------------------------------------------------
    |
    | Determine if Octane should watch for file changes and auto-reload the
    | server when file changes are detected. You may specify directories and
    | file extensions that should be watched by the server.
    |
    */

    'watch' => env('OCTANE_WATCH', false),

    'watched' => [
        'app',
        'config',
        'database',
        'routes',
        'storage/app',
        'resources/views',
    ],

    'watch_extensions' => [
        'php',
        'env',
    ],

    /*
    |--------------------------------------------------------------------------
    | Listeners
    |--------------------------------------------------------------------------
    |
    | Octane supports several events or "listeners" that may be registered
    | as your application boots or as you handle incoming requests. You
    | may modify the listeners or add your own to customize your app.
    |
    */

    'listeners' => [],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | By default, Octane will serve each request from the same application
    | instance. However, you may wish to have fresh instances per request.
    | Here, you may define caching options for the HTTP cache middleware.
    |
    */

    'cache' => [
        'tables' => ['routes'],
        'ttl' => env('OCTANE_CACHE_TTL', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Reload
    |--------------------------------------------------------------------------
    |
    | Configure the number of requests after which the worker process
    | should be reloaded. This helps prevent memory leaks and ensures
    | optimal performance of the application.
    |
    */

    'reload' => [
        'requests' => env('OCTANE_RELOAD_REQUESTS', 500),
        'mb' => env('OCTANE_RELOAD_MB', 256),
    ],

    /*
    |--------------------------------------------------------------------------
    | Container
    |--------------------------------------------------------------------------
    |
    | By default, Octane will use the same application container for each
    | request. However, you may toggle this behavior here. If true, the
    | container will be reset on each request.
    |
    */

    'container' => [
        'reset_between_requests' => env('OCTANE_CONTAINER_RESET', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Paging
    |--------------------------------------------------------------------------
    |
    | By default, this option allows you to disable Octane's memory limits
    | per worker. However, if you wish to set memory limits, you may do so
    | using this configuration option.
    |
    */

    'max_execution_time' => env('OCTANE_MAX_EXECUTION_TIME', 60),
];
