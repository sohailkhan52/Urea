<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Octane Server
    |--------------------------------------------------------------------------
    |
    | This value contains the name of the server that will serve the
    | application when running "php artisan octane:start".
    |
    */
    'server' => env('OCTANE_SERVER', 'roadrunner'),

    /*
    |--------------------------------------------------------------------------
    | Octane Workers
    |--------------------------------------------------------------------------
    |
    | The number of worker processes to spawn. By default this will be set
    | to 4 workers. Each worker will handle concurrent requests.
    |
    */
    'workers' => env('OCTANE_WORKERS', 4),

    /*
    |--------------------------------------------------------------------------
    | Task Workers
    |--------------------------------------------------------------------------
    |
    | Task workers handle long-running tasks spawned via
    | the Octane::task() method.
    |
    */
    'task_workers' => env('OCTANE_TASK_WORKERS', 4),

    /*
    |--------------------------------------------------------------------------
    | Max Requests
    |--------------------------------------------------------------------------
    |
    | The number of requests to process before reloading the worker
    | to prevent memory leaks from accumulating.
    |
    */
    'max_requests' => env('OCTANE_MAX_REQUESTS', 500),

    /*
    |--------------------------------------------------------------------------
    | Octane Cache
    |--------------------------------------------------------------------------
    |
    | Enable or disable the cached file changes for the Octane watcher.
    |
    */
    'cache' => true,

    /*
    |--------------------------------------------------------------------------
    | Octane Watch
    |--------------------------------------------------------------------------
    |
    | When Octane detects code changes, it will automatically reload
    | the workers. This is useful for local development.
    |
    */
    'watch' => env('OCTANE_WATCH', false),

    /*
    |--------------------------------------------------------------------------
    | Watch Paths
    |--------------------------------------------------------------------------
    |
    | Paths to watch for file changes when "watch" is enabled.
    |
    */
    'watch_paths' => [
        'app/',
        'bootstrap/',
        'config/',
        'routes/',
        'resources/views/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Watch Ignore Paths
    |--------------------------------------------------------------------------
    |
    | Paths to ignore when watching for file changes.
    |
    */
    'watch_ignore' => [
        'storage/',
        'vendor/',
        'node_modules/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache TTL
    |--------------------------------------------------------------------------
    |
    | The amount of time (in seconds) to cache file changes when "watch" is enabled.
    |
    */
    'cache_ttl' => env('OCTANE_CACHE_TTL', 60),

    /*
    |--------------------------------------------------------------------------
    | Reload on File Changes
    |--------------------------------------------------------------------------
    |
    | The number of file changes before auto-reloading the worker.
    |
    */
    'reload_on_file_changes' => env('OCTANE_RELOAD_ON_FILE_CHANGES', 1),

    /*
    |--------------------------------------------------------------------------
    | Graceful Shutdown Timeout
    |--------------------------------------------------------------------------
    |
    | The number of seconds to wait before force-killing worker processes
    | when gracefully shutting down.
    |
    */
    'graceful_shutdown_timeout' => env('OCTANE_GRACEFUL_SHUTDOWN_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Memory Limit
    |--------------------------------------------------------------------------
    |
    | The amount of memory (in MB) each worker process is allowed to use.
    | Workers will restart when this limit is reached.
    |
    */
    'memory_limit' => env('OCTANE_MEMORY_LIMIT', 256),

    /*
    |--------------------------------------------------------------------------
    | RoadRunner Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration specific to the RoadRunner server.
    |
    */
    'rpc' => [
        'host' => env('ROADRUNNER_RPC_HOST', 'localhost'),
        'port' => env('ROADRUNNER_RPC_PORT', 6001),
    ],

    /*
    |--------------------------------------------------------------------------
    | Swoole Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration specific to the Swoole server (if using instead of RoadRunner).
    |
    */
    'swoole' => [
        'host' => env('SWOOLE_HOST', '127.0.0.1'),
        'port' => env('SWOOLE_PORT', 8000),
        'options' => [
            'worker_num' => env('OCTANE_WORKERS', 4),
            'task_worker_num' => env('OCTANE_TASK_WORKERS', 4),
            'max_request' => env('OCTANE_MAX_REQUESTS', 500),
            'max_coroutine' => 1000,
            'enable_coroutine' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Listeners
    |--------------------------------------------------------------------------
    |
    | Event listeners to attach to Octane for lifecycle events.
    | TEMPORARILY DISABLED - Enable after Redis installation
    |
    */
    'listeners' => [
        // \App\Listeners\OctaneListener::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Container Reset
    |--------------------------------------------------------------------------
    |
    | Whether to reset the service container between requests.
    | Typically false for better performance, but true for safety.
    |
    */
    'container_reset' => env('OCTANE_CONTAINER_RESET', false),

    /*
    |--------------------------------------------------------------------------
    | Tick
    |--------------------------------------------------------------------------
    |
    | The tick interval (in milliseconds) to use for the event loop.
    |
    */
    'tick' => env('OCTANE_TICK', 0),

    /*
    |--------------------------------------------------------------------------
    | Max Execution Time
    |--------------------------------------------------------------------------
    |
    | The maximum execution time (in seconds) for a single request.
    |
    */
    'max_execution_time' => env('OCTANE_MAX_EXECUTION_TIME', 300),
];
