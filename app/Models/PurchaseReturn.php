<?php

namespace App\Models;

use App\Traits\WarehouseScopeable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Purchase Return Model
 * 
 * Represents products returned to suppliers from the warehouse.
 * Returns remove stock and create financial adjustments.
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
     * Return type constants
     */
    public const RETURN_TYPE_WHOLE_ORDER = 'WHOLE_ORDER';
    public const RETURN_TYPE_PARTIAL_ITEMS = 'PARTIAL_ITEMS';

    /**
     * Payment status constants
     */
    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_REFUNDED = 'refunded';
    public const PAYMENT_STATUS_CREDITED = 'credited';
    public const PAYMENT_STATUS_PARTIAL = 'partial';

    /**
     * Refund methods
     */
    public const REFUND_METHOD_CASH = 'cash';
    public const REFUND_METHOD_BANK_TRANSFER = 'bank_transfer';
    public const REFUND_METHOD_EASYPAISA = 'easypaisa';
    public const REFUND_METHOD_JAZZ_CASH = 'jazz_cash';
    public const REFUND_METHOD_CHEQUE = 'cheque';
    public const REFUND_METHOD_OTHER = 'other';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'return_number',
        'purchase_id',
        'supplier_id',
        'warehouse_id',
        'return_date',
        'return_type',
        'subtotal',
        'discount_adjustment',
        'total_amount',
        'refund_amount',
        'supplier_credit_amount',
        'refund_method',
        'refund_reference',
        'payment_status',
        'status',
        'reason',
        'notes',
        'created_by',
        'confirmed_by',
        'cancelled_by',
        'confirmed_at',
        'cancelled_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
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
            'discount_adjustment' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'supplier_credit_amount' => 'decimal:2',
        ];
    }

    /**
     * Get the original purchase
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class)->withTrashed();
    }

    /**
     * Get the supplier
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the warehouse
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get return items
     */
    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    /**
     * Get creator (user)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get confirmer (user)
     */
    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Get canceller (user)
     */
    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Get ledger entries
     */
    public function ledgerEntries()
    {
        return $this->hasMany(SupplierLedger::class, 'purchase_return_id');
    }

    /**
     * Get stock movements
     */
    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    /**
     * Check if return is draft
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
     * Get credit amount (alias for supplier_credit_amount)
     */
    public function getCreditAmountAttribute()
    {
        return $this->supplier_credit_amount;
    }

    /**
     * Check if this is a whole order return
     */
    public function isWholeOrderReturn(): bool
    {
        return $this->return_type === self::RETURN_TYPE_WHOLE_ORDER;
    }

    /**
     * Check if this is a partial items return
     */
    public function isPartialItemsReturn(): bool
    {
        return $this->return_type === self::RETURN_TYPE_PARTIAL_ITEMS;
    }

    /**
     * Get return type label
     */
    public function getReturnTypeLabelAttribute(): string
    {
        return match($this->return_type) {
            self::RETURN_TYPE_WHOLE_ORDER => 'Whole Order',
            self::RETURN_TYPE_PARTIAL_ITEMS => 'Partial Items',
            default => 'Unknown',
        };
    }

    /**
     * Check if return is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
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
        return $this->isConfirmed();
    }

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
     * Get payment status label
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return match($this->payment_status) {
            self::PAYMENT_STATUS_PENDING => 'Pending',
            self::PAYMENT_STATUS_REFUNDED => 'Refunded',
            self::PAYMENT_STATUS_CREDITED => 'Credited',
            self::PAYMENT_STATUS_PARTIAL => 'Partial',
            default => 'Unknown',
        };
    }

    /**
     * Get payment status badge class
     */
    public function getPaymentStatusBadgeAttribute(): string
    {
        return match($this->payment_status) {
            self::PAYMENT_STATUS_PENDING => 'secondary',
            self::PAYMENT_STATUS_REFUNDED => 'success',
            self::PAYMENT_STATUS_CREDITED => 'info',
            self::PAYMENT_STATUS_PARTIAL => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Scope to filter confirmed returns
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope to filter by purchase
     */
    public function scopeByPurchase($query, $purchaseId)
    {
        return $query->where('purchase_id', $purchaseId);
    }

    /**
     * Scope to filter by supplier
     */
    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('return_date', [$startDate, $endDate]);
    }

    /**
     * Get refund method label
     */
    public function getRefundMethodLabelAttribute(): ?string
    {
        if (!$this->refund_method) {
            return null;
        }

        return match($this->refund_method) {
            self::REFUND_METHOD_CASH => 'Cash',
            self::REFUND_METHOD_BANK_TRANSFER => 'Bank Transfer',
            self::REFUND_METHOD_EASYPAISA => 'EasyPaisa',
            self::REFUND_METHOD_JAZZ_CASH => 'Jazz Cash',
            self::REFUND_METHOD_CHEQUE => 'Cheque',
            self::REFUND_METHOD_OTHER => 'Other',
            default => ucfirst($this->refund_method),
        };
    }

    /**
     * Get total items count
     */
    public function getTotalItemsCountAttribute(): int
    {
        return $this->items()->count();
    }

    /**
     * Get total quantity
     */
    public function getTotalQuantityAttribute(): float
    {
        return $this->items()->sum('quantity');
    }
}
