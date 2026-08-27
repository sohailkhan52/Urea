<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * NotificationCreated Event
 *
 * Broadcast when a new notification is created.
 * Used for real-time notification delivery to clients.
 */
class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Notification $notification
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        // Broadcast to user's private notification channel
        return [
            new PrivateChannel('notifications.user.' . $this->notification->user_id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'type' => $this->notification->type,
            'type_label' => $this->notification->getTypeLabel(),
            'title' => $this->notification->getTitle(),
            'body' => $this->notification->getBody(),
            'icon' => $this->notification->getIcon(),
            'warehouse_id' => $this->notification->warehouse_id,
            'warehouse_name' => $this->notification->warehouse?->name,
            'related_type' => $this->notification->related_type,
            'related_id' => $this->notification->related_id,
            'route' => $this->notification->getRoute(),
            'data' => $this->notification->data,
            'created_at' => $this->notification->created_at->toIso8601String(),
            'created_at_formatted' => $this->notification->created_at->diffForHumans(),
        ];
    }

    /**
     * Get the name of the event.
     */
    public function broadcastAs(): string
    {
        return 'notification.created';
    }
}
