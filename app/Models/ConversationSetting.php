<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ConversationSetting Model
 * 
 * Stores user-specific settings for conversations.
 * 
 * @property int $id
 * @property int $conversation_id
 * @property int $user_id
 * @property bool $notifications_enabled
 * @property bool $archived
 * @property bool $muted
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ConversationSetting extends Model
{
    protected $fillable = [
        'conversation_id',
        'user_id',
        'notifications_enabled',
        'archived',
        'muted',
    ];

    protected $casts = [
        'notifications_enabled' => 'boolean',
        'archived' => 'boolean',
        'muted' => 'boolean',
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
}
