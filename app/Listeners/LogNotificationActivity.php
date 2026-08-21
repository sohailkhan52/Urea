<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogNotificationActivity implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle any event by logging notification activity.
     * This can be used for audit trails and analytics.
     */
    public function handle(object $event): void
    {
        $eventClass = class_basename($event::class);
        
        // Extract relevant data from event
        $data = [];
        foreach ($event as $key => $value) {
            if (is_object($value) && method_exists($value, 'getKey')) {
                $data[$key] = [
                    'model' => class_basename($value::class),
                    'id' => $value->getKey(),
                ];
            }
        }

        Log::info("Notification activity: {$eventClass}", $data);
    }
}
