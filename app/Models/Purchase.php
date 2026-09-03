<?php

namespace App\Models;

use App\Traits\WarehouseScopeable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Purchase Model - Matches create.blade.php view requirements
 * 
 * @property int $id
 * @property string $purchase_number
 * @property int $supplier_id
 * @property int $warehouse_id
 * @property \Illuminate\Support\Carbon $purchase_date
 * @property string $status
 * @property float $subtotal
 * @property string $discount_type
 * @property float $discount
 * @property float $transport_cost
 * @property float $other_expenses
 * @property float $total_amount
 * @property float $paid_amount
 * @property string $payment_status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $confirmed_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property int $created_by
 * @property int|null $confirmed_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * 
 * @property-read Supplier $supplier
 * @property-read Warehouse $warehouse
 * @property-read \Illuminate\Database\Eloquent\Collection|PurchaseItem[] $items
 * @property-read User $creator
 * @property-read User|null $confirmer
 * @property-read \Illuminate\Database\Eloquent\Collection|PurchasePayment[] $payments
 */
class Purchase extends Model
{
    use HasFactory, SoftDeletes, WarehouseScopeable;

    /**
     * Status constants - matching migration enum
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Payment status constants - matching migration enum
     */
    public const PAYMENT_STATUS_UNPAID = 'unpaid';
    public const PAYMENT_STATUS_PARTIAL = 'partial';
    public const PAYMENT_STATUS_PAID = 'paid';

    /**
     * Discount type constants - matching view form
     */
    public const DISCOUNT_TYPE_AMOUNT = 'amount';
    public const DISCOUNT_TYPE_PERCENTAGE = 'percentage';

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
        'discount_type',
        'discount',
        'transport_cost',
        'other_expenses',
        'total_amount',
        'paid_amount',
        'payment_status',
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

    // ========== RELATIONSHIPS ==========

    /**
     * Get the supplier that owns the purchase
     */
    public function supplier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the warehouse that owns the purchase
     */
    public function warehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the purchase items (products with quantity, prices)
     */
    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Get the user who created this purchase
     */
    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who confirmed this purchase
     */
    public function confirmer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Get purchase payments history
     */
    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchasePayment::class);
    }

    // ========== STATUS CHECK METHODS ==========

    /**
     * Check if purchase is in draft status
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
     * Check if purchase is fully paid
     */
    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    /**
     * Check if purchase is partially paid
     */
    public function isPartial(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PARTIAL;
    }

    /**
     * Check if purchase is unpaid
     */
    public function isUnpaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_UNPAID;
    }

    // ========== PERMISSION METHODS ==========

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

    // ========== QUERY SCOPES ==========

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
     * Scope to filter by payment status
     */
    public function scopeByPaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope to filter unpaid purchases
     */
    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_UNPAID);
    }

    /**
     * Scope to filter partially paid purchases
     */
    public function scopePartial($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PARTIAL);
    }

    /**
     * Scope to filter fully paid purchases
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PAID);
    }

    // ========== COMPUTED ATTRIBUTES ==========

    /**
     * Get the calculated discount amount (handles both amount and percentage)
     */
    public function getDiscountAmountAttribute(): float
    {
        if ($this->discount_type === self::DISCOUNT_TYPE_PERCENTAGE) {
            return ($this->subtotal * $this->discount) / 100;
        }
        
        return (float) $this->discount;
    }

    /**
     * Get remaining payable amount (total - paid)
     */
    public function getPayableAmountAttribute(): float
    {
        return max(0, $this->total_amount - $this->paid_amount);
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
     * Get status label for display
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
     * Get status badge class for Bootstrap
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
     * Get payment status label for display
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return match($this->payment_status) {
            self::PAYMENT_STATUS_PAID => 'Paid',
            self::PAYMENT_STATUS_PARTIAL => 'Partial',
            self::PAYMENT_STATUS_UNPAID => 'Unpaid',
            default => 'Unknown',
        };
    }

    /**
     * Get payment status badge class for Bootstrap
     */
    public function getPaymentStatusBadgeAttribute(): string
    {
        return match($this->payment_status) {
            self::PAYMENT_STATUS_PAID => 'success',
            self::PAYMENT_STATUS_PARTIAL => 'warning',
            self::PAYMENT_STATUS_UNPAID => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get discount type options for dropdown
     */
    public static function getDiscountTypes(): array
    {
        return [
            self::DISCOUNT_TYPE_AMOUNT => 'Amount (Rs.)',
            self::DISCOUNT_TYPE_PERCENTAGE => 'Percentage (%)',
        ];
    }

    // ========== HELPER METHODS ==========

    /**
     * Calculate and update the total amount based on subtotal, discount, transport, and other expenses
     * Matches the calculation logic in the view
     */
    public function calculateTotalAmount(): float
    {
        $discountAmount = $this->discount_amount;
        
        $total = $this->subtotal 
                 - $discountAmount 
                 + $this->transport_cost 
                 + $this->other_expenses;
        
        return max(0, $total);
    }

    /**
     * Update payment status based on paid amount vs total amount
     * This should be called whenever paid_amount or total_amount changes
     */
    public function updatePaymentStatus(): void
    {
        if ($this->paid_amount <= 0) {
            $this->payment_status = self::PAYMENT_STATUS_UNPAID;
        } elseif ($this->paid_amount >= $this->total_amount) {
            $this->payment_status = self::PAYMENT_STATUS_PAID;
        } else {
            $this->payment_status = self::PAYMENT_STATUS_PARTIAL;
        }
    }

    /**
     * Recalculate subtotal from items
     */
    public function recalculateSubtotal(): float
    {
        return $this->items()->sum(\DB::raw('quantity * purchase_price'));
    }
}
