<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message)
    {
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->message->conversation_id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->name,
            'sender_avatar' => $this->message->sender->avatar_url ?? null,
            'message' => htmlspecialchars($this->message->message, ENT_QUOTES, 'UTF-8'),
            'message_type' => $this->message->message_type,
            'related_type' => $this->message->related_type,
            'related_id' => $this->message->related_id,
            'created_at' => $this->message->created_at->toIso8601String(),
            'created_at_formatted' => $this->message->created_at->format('H:i'),
            'created_date_formatted' => $this->message->created_at->format('M d'),
        ];
    }

    /**
     * Get the name of the event.
     */
    public function broadcastAs(): string
    {
        return 'MessageSent';
    }
}
