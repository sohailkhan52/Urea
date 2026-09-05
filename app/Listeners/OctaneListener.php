<?php

namespace App\Listeners;

use Laravel\Octane\Events\RequestReceived;
use Laravel\Octane\Events\RequestHandled;
use Laravel\Octane\Events\WorkerStarting;
use Laravel\Octane\Events\WorkerStopping;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * OctaneListener - Manages Octane worker lifecycle
 * 
 * Ensures proper resource cleanup and state management
 * across long-running worker processes
 */
class OctaneListener
{
    /**
     * Handle worker starting event
     * Initialize worker state
     * 
     * @param WorkerStarting $event
     */
    public function handleWorkerStarting(WorkerStarting $event): void
    {
        Log::info('Octane worker starting');
        
        // Verify database connection
        try {
            DB::connection()->getPdo();
            Log::info('Database connection verified');
        } catch (\Exception $e) {
            Log::error('Database connection failed', ['error' => $e->getMessage()]);
        }

        // Verify Redis connection
        try {
            Cache::store('redis')->connection()->ping();
            Log::info('Redis connection verified');
        } catch (\Exception $e) {
            Log::error('Redis connection failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle worker stopping event
     * Cleanup resources
     * 
     * @param WorkerStopping $event
     */
    public function handleWorkerStopping(WorkerStopping $event): void
    {
        Log::info('Octane worker stopping');
        
        // Close database connections
        DB::disconnect();
        
        // Clear any temporary state
        // Do NOT clear financial caches
    }

    /**
     * Handle request received event
     * Initialize request state
     * 
     * @param RequestReceived $event
     */
    public function handleRequestReceived(RequestReceived $event): void
    {
        // Reset any request-specific state
        // But DO NOT:
        // - Clear financial caches
        // - Reset database state
        // - Clear user auth
    }

    /**
     * Handle request handled event
     * Cleanup after request
     * 
     * @param RequestHandled $event
     */
    public function handleRequestHandled(RequestHandled $event): void
    {
        // Reconnect to database if needed
        if (!DB::connection()->getPdo()) {
            DB::reconnect();
        }

        // Log slow requests
        $duration = microtime(true) - LARAVEL_START;
        if ($duration > 1.0) {
            Log::warning('Slow request detected', [
                'duration' => $duration,
                'path' => $event->request->getPathInfo(),
            ]);
        }

        // Optional: Check memory usage
        $memory = memory_get_usage(true) / 1024 / 1024;
        if ($memory > 200) { // 200MB threshold
            Log::warning('High memory usage', ['memory_mb' => $memory]);
        }
    }
}
