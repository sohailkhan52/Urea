<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Broadcasting\PrivateChannel;

/**
 * Message Model
 * 
 * Represents a message in a warehouse conversation.
 * 
 * @property int $id
 * @property int $conversation_id
 * @property int $sender_id
 * @property string $message
 * @property string $message_type
 * @property string|null $related_type
 * @property int|null $related_id
 * @property \Carbon\Carbon|null $read_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message',
        'message_type',
        'related_type',
        'related_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'is_read',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the conversation this message belongs to
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the sender of this message
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get users who have read this message
     */
    public function reads(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'message_reads')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Get messages in chronological order
     */
    public function scopeChronological($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    /**
     * Get only text messages
     */
    public function scopeTextOnly($query)
    {
        return $query->where('message_type', 'text');
    }

    /**
     * Get messages after a specific date
     */
    public function scopeAfter($query, $date)
    {
        return $query->where('created_at', '>', $date);
    }

    /**
     * Get unread messages for a user
     */
    public function scopeUnreadBy($query, User $user)
    {
        return $query->whereDoesntHave('reads', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('sender_id', '!=', $user->id);
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if message is read by user
     */
    public function isReadBy(User $user): bool
    {
        return $this->reads()->where('user_id', $user->id)->exists();
    }

    /**
     * Mark message as read by user
     */
    public function markAsReadBy(User $user): void
    {
        if (!$this->isReadBy($user)) {
            $this->reads()->attach($user->id, [
                'read_at' => now(),
            ]);
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Get is_read attribute
     */
    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Check if user can access this message
     */
    public function canAccess(User $user): bool
    {
        return $this->conversation->canAccess($user);
    }

    /**
     * Get display name for sender
     */
    public function getSenderNameAttribute(): string
    {
        return $this->sender->name ?? 'Unknown';
    }

    /**
     * Get formatted message for display
     */
    public function getFormattedMessageAttribute(): string
    {
        return htmlspecialchars($this->message, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get message type label
     */
    public function getMessageTypeLabelAttribute(): string
    {
        return match($this->message_type) {
            'text' => 'Text',
            'system' => 'System',
            'mention' => 'Mention',
            default => ucfirst($this->message_type),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Broadcasting
    |--------------------------------------------------------------------------
    */

    /**
     * Get the channels that model events should broadcast on
     */
    public function broadcastOn(?string $events = null): array
    {
        return match($events) {
            'created' => [
                new PrivateChannel("conversation.{$this->conversation_id}"),
            ],
            default => [],
        };
    }

    /**
     * Get the data to broadcast
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_id' => $this->sender_id,
            'sender_name' => $this->sender->name,
            'sender_avatar' => $this->sender->avatar_url ?? null,
            'message' => $this->formatted_message,
            'message_type' => $this->message_type,
            'created_at' => $this->created_at->toIso8601String(),
            'created_at_formatted' => $this->created_at->format('H:i'),
        ];
    }

    /**
     * Get the name of the event
     */
    public function broadcastAs(): string
    {
        return 'MessageSent';
    }
}
