<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $purchase_number
 * @property int $supplier_id
 * @property int $warehouse_id
 * @property \Illuminate\Support\Carbon $purchase_date
 * @property string $status
 * @property float $subtotal
 * @property float $discount
 * @property float $transport_cost
 * @property float $other_expenses
 * @property float $total_amount
 * @property float $paid_amount
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $confirmed_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property int $created_by
 * @property int|null $confirmed_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Status constants
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'purchase_number',
        'supplier_id',
        'warehouse_id',
        'purchase_date',
        'status',
        'subtotal',
        'discount',
        'transport_cost',
        'other_expenses',
        'total_amount',
        'paid_amount',
        'notes',
        'confirmed_at',
        'cancelled_at',
        'created_by',
        'confirmed_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'transport_cost' => 'decimal:2',
            'other_expenses' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    /**
     * Check if purchase is draft
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if purchase is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if purchase is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Get supplier relationship
     */
    public function supplier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get warehouse relationship
     */
    public function warehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get purchase items
     */
    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Get creator (user)
     */
    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get confirmer (user)
     */
    public function confirmer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Scope to filter draft purchases
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope to filter confirmed purchases
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope to filter cancelled purchases
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope to filter by supplier
     */
    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Scope to filter by warehouse
     */
    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
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

    /**
     * Check if purchase can be edited
     */
    public function canBeEdited(): bool
    {
        return $this->isDraft();
    }

    /**
     * Check if purchase can be confirmed
     */
    public function canBeConfirmed(): bool
    {
        return $this->isDraft() && $this->items()->count() > 0;
    }

    /**
     * Check if purchase can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return !$this->isCancelled();
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
     * Get payment status
     */
    public function getPaymentStatusAttribute(): string
    {
        if ($this->paid_amount == 0) {
            return 'Unpaid';
        } elseif ($this->paid_amount >= $this->total_amount) {
            return 'Paid';
        } else {
            return 'Partial';
        }
    }

    /**
     * Get balance amount
     */
    public function getBalanceAttribute(): float
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }
}
