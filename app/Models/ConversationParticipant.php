<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ConversationParticipant Model
 * 
 * Represents a user's participation in a conversation.
 * 
 * @property int $id
 * @property int $conversation_id
 * @property int $user_id
 * @property \Carbon\Carbon|null $last_read_at
 * @property \Carbon\Carbon $joined_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ConversationParticipant extends Model
{
    protected $fillable = [
        'conversation_id',
        'user_id',
        'last_read_at',
        'joined_at',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
        'joined_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the conversation
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get unread message count
     */
    public function unreadCount(): int
    {
        if (!$this->last_read_at) {
            // User hasn't read anything, count all messages from others
            return $this->conversation
                ->messages()
                ->where('sender_id', '!=', $this->user_id)
                ->count();
        }

        // Count messages after last read
        return $this->conversation
            ->messages()
            ->where('created_at', '>', $this->last_read_at)
            ->where('sender_id', '!=', $this->user_id)
            ->count();
    }
}
