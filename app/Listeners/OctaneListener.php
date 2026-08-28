<?php

namespace App\Listeners;

use Laravel\Octane\Events\TickReceived;
use Laravel\Octane\Events\WorkerErrorOccurred;
use Laravel\Octane\Events\RequestReceived;
use Laravel\Octane\Events\RequestHandled;
use Illuminate\Support\Facades\Log;

class OctaneListener
{
    /**
     * Handle the request received event.
     */
    public function onRequestReceived(RequestReceived $event): void
    {
        // Reset any per-request state if needed
        // This fires before each request is processed
    }

    /**
     * Handle the request handled event.
     */
    public function onRequestHandled(RequestHandled $event): void
    {
        // Clean up after each request if needed
        // This fires after each request has been processed
    }

    /**
     * Handle the tick received event (runs periodically).
     */
    public function onTickReceived(TickReceived $event): void
    {
        // Run periodic tasks (like health checks)
        // This fires every 5 seconds
    }

    /**
     * Handle worker errors.
     */
    public function onWorkerErrorOccurred(WorkerErrorOccurred $event): void
    {
        Log::error('Octane Worker Error', [
            'exception' => $event->exception,
        ]);
    }
}
