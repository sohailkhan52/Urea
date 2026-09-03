<?php

namespace App\Models;

use App\Traits\WarehouseScopeable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Purchase Return Model
 * 
 * @property int $id
 * @property string $return_number
 * @property int $purchase_id
 * @property int $supplier_id
 * @property int $warehouse_id
 * @property \Illuminate\Support\Carbon $return_date
 * @property string $status
 * @property float $subtotal
 * @property float $transport_cost
 * @property float $total_amount
 * @property float $refund_amount
 * @property string $refund_status
 * @property string|null $reason
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $confirmed_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property int $created_by
 * @property int|null $confirmed_by
 */
class PurchaseReturn extends Model
{
    use HasFactory, SoftDeletes, WarehouseScopeable;

    /**
     * Status constants
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Refund status constants
     */
    public const REFUND_STATUS_PENDING = 'pending';
    public const REFUND_STATUS_PARTIAL = 'partial';
    public const REFUND_STATUS_COMPLETED = 'completed';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'return_number',
        'purchase_id',
        'supplier_id',
        'warehouse_id',
        'return_date',
        'status',
        'subtotal',
        'total_amount',
        'refund_amount',
        'refund_status',
        'reason',
        'notes',
        'confirmed_at',
        'cancelled_at',
        'created_by',
        'confirmed_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'return_date' => 'date',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
        ];
    }

    // ========== RELATIONSHIPS ==========

    /**
     * Get the original purchase
     */
    public function purchase(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get the supplier
     */
    public function supplier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the warehouse
     */
    public function warehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the return items
     */
    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    /**
     * Get the user who created this return
     */
    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who confirmed this return
     */
    public function confirmer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // ========== STATUS CHECK METHODS ==========

    /**
     * Check if return is in draft status
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if return is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if return is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if refund is pending
     */
    public function isRefundPending(): bool
    {
        return $this->refund_status === self::REFUND_STATUS_PENDING;
    }

    /**
     * Check if refund is partial
     */
    public function isRefundPartial(): bool
    {
        return $this->refund_status === self::REFUND_STATUS_PARTIAL;
    }

    /**
     * Check if refund is completed
     */
    public function isRefundCompleted(): bool
    {
        return $this->refund_status === self::REFUND_STATUS_COMPLETED;
    }

    // ========== PERMISSION METHODS ==========

    /**
     * Check if return can be edited
     */
    public function canBeEdited(): bool
    {
        return $this->isDraft();
    }

    /**
     * Check if return can be confirmed
     */
    public function canBeConfirmed(): bool
    {
        return $this->isDraft() && $this->items()->count() > 0;
    }

    /**
     * Check if return can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return !$this->isCancelled();
    }

    // ========== QUERY SCOPES ==========

    /**
     * Scope to filter draft returns
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope to filter confirmed returns
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope to filter cancelled returns
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope to filter by refund status
     */
    public function scopeByRefundStatus($query, $status)
    {
        return $query->where('refund_status', $status);
    }

    // ========== COMPUTED ATTRIBUTES ==========

    /**
     * Get remaining refund amount
     */
    public function getRemainingRefundAttribute(): float
    {
        return max(0, $this->total_amount - $this->refund_amount);
    }

    /**
     * Get total items count
     */
    public function getTotalItemsCountAttribute(): int
    {
        return $this->items()->count();
    }

    /**
     * Get total quantity of all items
     */
    public function getTotalQuantityAttribute(): float
    {
        return $this->items()->sum('quantity');
    }

    // ========== DISPLAY ATTRIBUTES ==========

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Unknown',
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'warning',
            self::STATUS_CONFIRMED => 'success',
            self::STATUS_CANCELLED => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get refund status label
     */
    public function getRefundStatusLabelAttribute(): string
    {
        return match($this->refund_status) {
            self::REFUND_STATUS_COMPLETED => 'Completed',
            self::REFUND_STATUS_PARTIAL => 'Partial',
            self::REFUND_STATUS_PENDING => 'Pending',
            default => 'Unknown',
        };
    }

    /**
     * Get refund status badge class
     */
    public function getRefundStatusBadgeAttribute(): string
    {
        return match($this->refund_status) {
            self::REFUND_STATUS_COMPLETED => 'success',
            self::REFUND_STATUS_PARTIAL => 'warning',
            self::REFUND_STATUS_PENDING => 'danger',
            default => 'secondary',
        };
    }

    // ========== HELPER METHODS ==========

    /**
     * Calculate and update the total amount
     */
    public function calculateTotalAmount(): float
    {
        return $this->subtotal;
    }

    /**
     * Update refund status based on refund amount
     */
    public function updateRefundStatus(): void
    {
        if ($this->refund_amount <= 0) {
            $this->refund_status = self::REFUND_STATUS_PENDING;
        } elseif ($this->refund_amount >= $this->total_amount) {
            $this->refund_status = self::REFUND_STATUS_COMPLETED;
        } else {
            $this->refund_status = self::REFUND_STATUS_PARTIAL;
        }
    }

    /**
     * Recalculate subtotal from items
     */
    public function recalculateSubtotal(): float
    {
        return $this->items()->sum(\DB::raw('quantity * unit_price'));
    }
}
