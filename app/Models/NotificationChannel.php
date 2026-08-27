<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationChannel extends Model
{
    protected $fillable = [
        'notification_id',
        'channel',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];

    /**
     * Get the notification
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }

    /**
     * Mark as sent
     */
    public function markAsSent(): void
    {
        $this->update(['status' => 'sent']);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $error = null): void
    {
        $this->update([
            'status' => 'failed',
            'metadata' => array_merge($this->metadata ?? [], ['error' => $error]),
        ]);
    }

    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        $this->update(['status' => 'read']);
    }
}
