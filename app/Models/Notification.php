<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\AsCollection;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'warehouse_id',
        'related_type',
        'related_id',
        'data',
        'read_at',
        'status',
    ];

    protected $casts = [
        'data' => 'json',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who receives this notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the warehouse context
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get notification channels for tracking delivery
     */
    public function channels(): HasMany
    {
        return $this->hasMany(NotificationChannel::class);
    }

    /**
     * Scope: Get unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    /**
     * Scope: Get read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('status', 'read');
    }

    /**
     * Scope: Get for specific user
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Scope: Get for user's warehouses (respects multi-warehouse)
     */
    public function scopeForUserWarehouses($query, User $user)
    {
        // Super Admin sees all
        if ($user->isSuperAdmin()) {
            return $query;
        }

        // Regular Admin sees only their warehouse
        $warehouseId = $user->warehouse_id ?? null;
        return $query->where(function ($q) use ($warehouseId) {
            $q->whereNull('warehouse_id')
              ->orWhere('warehouse_id', $warehouseId);
        });
    }

    /**
     * Scope: Get by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Get recent notifications (last 30 days)
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDays(30));
    }

    /**
     * Mark as read
     */
    public function markAsRead(): bool
    {
        if ($this->status === 'read') {
            return false;
        }

        return $this->update([
            'status' => 'read',
            'read_at' => now(),
        ]);
    }

    /**
     * Mark as unread
     */
    public function markAsUnread(): bool
    {
        return $this->update([
            'status' => 'unread',
            'read_at' => null,
        ]);
    }

    /**
     * Get human-readable type label
     */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            'stock_request_created' => 'New Stock Request',
            'stock_request_approved' => 'Stock Request Approved',
            'stock_request_partially_approved' => 'Stock Request Partially Approved',
            'stock_request_rejected' => 'Stock Request Rejected',
            'stock_request_cancelled' => 'Stock Request Cancelled',
            'stock_transfer_created' => 'Stock Transfer Created',
            'stock_transfer_dispatched' => 'Stock Transfer Dispatched',
            'stock_transfer_received' => 'Stock Transfer Received',
            'chat_message' => 'New Message',
            'warehouse_update' => 'Warehouse Update',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    /**
     * Get the title for notification display
     */
    public function getTitle(): string
    {
        return $this->data['title'] ?? $this->getTypeLabel();
    }

    /**
     * Get the body/description for notification display
     */
    public function getBody(): string
    {
        return $this->data['body'] ?? $this->data['message'] ?? '';
    }

    /**
     * Get the icon for notification display
     */
    public function getIcon(): string
    {
        return match($this->type) {
            'stock_request_created' => 'bi-file-earmark-plus',
            'stock_request_approved' => 'bi-check-circle',
            'stock_request_partially_approved' => 'bi-check-square',
            'stock_request_rejected' => 'bi-x-circle',
            'stock_request_cancelled' => 'bi-dash-circle',
            'stock_transfer_created' => 'bi-box-seam',
            'stock_transfer_dispatched' => 'bi-truck',
            'stock_transfer_received' => 'bi-inbox',
            'chat_message' => 'bi-chat-dots',
            'warehouse_update' => 'bi-building',
            default => 'bi-bell',
        };
    }

    /**
     * Get the related entity (Stock Request, Transfer, Message, etc.)
     */
    public function getRelated(): ?Model
    {
        if (!$this->related_type || !$this->related_id) {
            return null;
        }

        $modelClass = 'App\\Models\\' . ucfirst($this->related_type);
        
        if (!class_exists($modelClass)) {
            return null;
        }

        return $modelClass::find($this->related_id);
    }

    /**
     * Get route to view the related entity
     */
    public function getRoute(): ?string
    {
        return match($this->related_type) {
            'stock_request' => route('admin.stock-requests.show', $this->related_id),
            'stock_transfer' => route('admin.stock-transfers.show', $this->related_id),
            'conversation' => route('admin.chat.show', $this->related_id),
            default => null,
        };
    }
}
