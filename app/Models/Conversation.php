<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Conversation Model
 * 
 * Represents a warehouse conversation between Super Admin and Regular Admin.
 * 
 * @property int $id
 * @property int $warehouse_id
 * @property string $type
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'type',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the warehouse this conversation belongs to
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the user who created this conversation
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all participants in this conversation
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot('last_read_at', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Get all messages in this conversation
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)
            ->orderBy('created_at', 'asc');
    }

    /**
     * Get conversation participant records
     */
    public function conversationParticipants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    /**
     * Get the latest message
     */
    public function latestMessage()
    {
        return $this->hasOne(Message::class)
            ->latest('created_at');
    }

    /**
     * Get conversation settings
     */
    public function settings(): HasMany
    {
        return $this->hasMany(ConversationSetting::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Get conversations for a specific warehouse
     */
    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Get active conversations (warehouse and participants active)
     */
    public function scopeActive($query)
    {
        return $query->whereHas('warehouse', function ($q) {
            $q->where('status', 'active');
        });
    }

    /**
     * Get conversations for user (considering warehouse access)
     */
    public function scopeForUser($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query; // Super Admin sees all
        }

        // Regular Admin sees only their warehouse conversation
        return $query->where('warehouse_id', $user->warehouse_id);
    }

    /**
     * Get conversations accessible by user
     */
    public function scopeAccessibleBy($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query->active();
        }

        // Regular Admin sees conversations where they are a participant
        return $query->whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->active();
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if user can access this conversation
     */
    public function canAccess(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return $this->warehouse->status === 'active';
        }

        // Regular Admin can only access their warehouse's conversation
        return $this->warehouse_id === $user->warehouse_id 
            && $this->warehouse->status === 'active';
    }

    /**
     * Get unread message count for user
     */
    public function unreadCount(User $user): int
    {
        return $this->messages()
            ->whereDoesntHave('reads', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('sender_id', '!=', $user->id)
            ->count();
    }

    /**
     * Get last read message for user
     */
    public function lastReadMessage(User $user): ?Message
    {
        $participant = $this->conversationParticipants()
            ->where('user_id', $user->id)
            ->first();

        if (!$participant || !$participant->last_read_at) {
            return null;
        }

        return $this->messages()
            ->where('created_at', '<=', $participant->last_read_at)
            ->latest('created_at')
            ->first();
    }

    /**
     * Mark as read for user
     */
    public function markAsRead(User $user, ?Message $upToMessage = null): void
    {
        $participant = $this->conversationParticipants()
            ->where('user_id', $user->id)
            ->first();

        if (!$participant) {
            return;
        }

        $readAt = $upToMessage ? $upToMessage->created_at : now();

        $participant->update(['last_read_at' => $readAt]);

        // Mark messages as read
        if ($upToMessage) {
            $this->messages()
                ->where('created_at', '<=', $readAt)
                ->where('sender_id', '!=', $user->id)
                ->each(function (Message $message) use ($user) {
                    $message->markAsReadBy($user);
                });
        }
    }

    /**
     * Get the broadcast channel name for this conversation
     */
    public function getBroadcastChannel(): string
    {
        return "conversation.{$this->id}";
    }

    /**
     * Get the private broadcast channel name
     */
    public function getPrivateChannel(): string
    {
        return "private-conversation.{$this->id}";
    }
}
