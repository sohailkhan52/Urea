<?php

namespace App\Listeners;

use App\Events\NotificationCreated;
use App\Models\NotificationChannel;
use App\Mail\NotificationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * SendNotificationEmail Listener
 *
 * Handles email delivery for notifications.
 * Queued to avoid blocking the request.
 */
class SendNotificationEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(NotificationCreated $event): void
    {
        $notification = $event->notification;
        $user = $notification->user;

        // Get email channels
        $emailChannels = $notification->channels()
            ->where('channel', 'email')
            ->get();

        foreach ($emailChannels as $channel) {
            try {
                // Send email
                Mail::to($user->email)->send(new NotificationMail($notification));

                // Mark channel as sent
                $channel->markAsSent();

                Log::info("Notification email sent successfully", [
                    'notification_id' => $notification->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'notification_type' => $notification->type,
                ]);
            } catch (\Exception $e) {
                // Mark channel as failed
                $channel->markAsFailed($e->getMessage());

                Log::error("Failed to send notification email", [
                    'notification_id' => $notification->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'notification_type' => $notification->type,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
