<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'notify_stock_requests',
        'notify_stock_transfers',
        'notify_messages',
        'notify_warehouse_updates',
        'send_email',
        'send_in_app',
        'enable_browser_notifications',
        'do_not_disturb',
        'dnd_start',
        'dnd_end',
    ];

    protected $casts = [
        'notify_stock_requests' => 'boolean',
        'notify_stock_transfers' => 'boolean',
        'notify_messages' => 'boolean',
        'notify_warehouse_updates' => 'boolean',
        'send_email' => 'boolean',
        'send_in_app' => 'boolean',
        'enable_browser_notifications' => 'boolean',
        'do_not_disturb' => 'boolean',
        'dnd_start' => 'datetime:H:i',
        'dnd_end' => 'datetime:H:i',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if currently in do not disturb window
     */
    public function isInDoNotDisturbWindow(): bool
    {
        if (!$this->do_not_disturb) {
            return false;
        }

        if (!$this->dnd_start || !$this->dnd_end) {
            return false;
        }

        $now = now();
        $start = $now->clone()->setTimeFromTimeString($this->dnd_start->format('H:i'));
        $end = $now->clone()->setTimeFromTimeString($this->dnd_end->format('H:i'));

        if ($start->lessThan($end)) {
            // Normal range (e.g., 22:00 to 08:00)
            return $now->between($start, $end);
        }

        // Range crosses midnight (e.g., 22:00 to 06:00)
        return $now->greaterThanOrEqualTo($start) || $now->lessThanOrEqualTo($end);
    }

    /**
     * Check if user should receive notification
     */
    public function shouldNotify(string $type): bool
    {
        // Check if in do not disturb
        if ($this->isInDoNotDisturbWindow()) {
            return false;
        }

        // Check notification type preference
        return match($type) {
            'stock_request' => $this->notify_stock_requests,
            'stock_transfer' => $this->notify_stock_transfers,
            'message' => $this->notify_messages,
            'warehouse_update' => $this->notify_warehouse_updates,
            default => true,
        };
    }

    /**
     * Check if user should receive via channel
     */
    public function shouldNotifyVia(string $channel): bool
    {
        return match($channel) {
            'email' => $this->send_email,
            'in_app' => $this->send_in_app,
            'browser' => $this->enable_browser_notifications && $this->send_in_app,
            default => false,
        };
    }
}
