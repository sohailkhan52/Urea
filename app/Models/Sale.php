<?php

namespace App\Models;

use App\Traits\WarehouseScopeable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $invoice_number
 * @property int|null $customer_id
 * @property string|null $walkin_customer_name
 * @property string|null $walkin_customer_contact
 * @property int $warehouse_id
 * @property \Illuminate\Support\Carbon $sale_date
 * @property string $status
 * @property float $subtotal
 * @property float $discount
 * @property float $total_amount
 * @property float $paid_amount
 * @property float $due_amount
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $confirmed_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property int $created_by
 * @property int|null $confirmed_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Sale extends Model
{
    use HasFactory, SoftDeletes, WarehouseScopeable;

    /**
     * Status constants
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Payment Status constants
     */
    public const PAYMENT_STATUS_UNPAID = 'unpaid';
    public const PAYMENT_STATUS_PARTIAL = 'partial';
    public const PAYMENT_STATUS_PAID = 'paid';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'family_id',
        'walkin_customer_name',
        'walkin_customer_contact',
        'warehouse_id',
        'sale_date',
        'status',
        'subtotal',
        'discount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'payment_status',
        'udhar_amount',
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
            'sale_date' => 'date',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_amount' => 'decimal:2',
            'udhar_amount' => 'decimal:2',
            'payment_status' => 'string',
        ];
    }

    /**
     * Check if sale is draft
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if sale is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if sale is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Get all customer payments for this sale (after-sale payments)
     */
    public function customerPayments()
    {
        return $this->hasMany(CustomerPayment::class);
    }

    /**
     * Get all payments for this sale (legacy, if exists)
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get total additional payments made after sale creation
     */
    public function getTotalAdditionalPaymentsAttribute(): float
    {
        // FIX: Use cached aggregate instead of query to avoid N+1 when accessed in loops
        // This value should be pre-loaded with ->withSum('customerPayments', 'amount') when querying sales
        // Fallback to query if not pre-loaded (slower but safe)
        return (float)($this->attributes['customer_payments_sum_amount'] ?? $this->customerPayments()->sum('amount') ?? 0);
    }

    /**
     * Get current remaining udhar considering additional payments
     */
    public function getCurrentRemainingUdharAttribute(): float
    {
        $totalPaid = $this->paid_amount + $this->total_additional_payments;
        return max(0, $this->total_amount - $totalPaid);
    }

    /**
     * Get current payment status considering additional payments
     */
    public function getCurrentPaymentStatusAttribute(): string
    {
        $totalPaid = $this->paid_amount + $this->total_additional_payments;
        
        if ($totalPaid == 0) {
            return self::PAYMENT_STATUS_UNPAID;
        } elseif ($totalPaid >= $this->total_amount) {
            return self::PAYMENT_STATUS_PAID;
        } else {
            return self::PAYMENT_STATUS_PARTIAL;
        }
    }

    /**
     * Get udhar history for this sale
     */
    public function udharHistory()
    {
        return $this->hasMany(UdharHistory::class);
    }

    /**
     * Check if sale is fully paid
     */
    public function isPaid(): bool
    {
        return $this->paid_amount >= $this->total_amount;
    }

    /**
     * Check if sale is partially paid
     */
    public function isPartiallyPaid(): bool
    {
        return $this->paid_amount > 0 && !$this->isPaid();
    }

    /**
     * Check if sale is unpaid
     */
    public function isUnpaid(): bool
    {
        return $this->paid_amount == 0;
    }

    /**
     * Check if sale has outstanding credit (Udhar)
     */
    public function hasUdhar(): bool
    {
        return $this->udhar_amount > 0;
    }

    /**
     * Calculate payment status based on paid amount
     */
    public function calculatePaymentStatus(): string
    {
        if ($this->paid_amount == 0) {
            return self::PAYMENT_STATUS_UNPAID;
        } elseif ($this->paid_amount >= $this->total_amount) {
            return self::PAYMENT_STATUS_PAID;
        } else {
            return self::PAYMENT_STATUS_PARTIAL;
        }
    }

    /**
     * Get remaining payable amount
     */
    public function getRemainingPayableAmount(): float
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    /**
     * Get customer relationship
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    /**
     * Get family relationship
     */
    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    /**
     * Get warehouse relationship
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get sale items
     */
    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Get sale returns
     */
    public function returns()
    {
        return $this->hasMany(SaleReturn::class);
    }

    /**
     * Get total returned amount
     */
    public function getTotalReturnedAmountAttribute(): float
    {
        return $this->returns()->where('status', 'confirmed')->sum('total_return_amount');
    }

    /**
     * Get net sale amount after returns
     */
    public function getNetSaleAmountAttribute(): float
    {
        return $this->total_amount - $this->total_returned_amount;
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
     * Scope to filter draft sales
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope to filter confirmed sales
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope to filter cancelled sales
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope to filter by customer
     */
    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope to filter by warehouse
     */
    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('sale_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by payment status
     */
    public function scopeByPaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope to filter sales with outstanding udhar
     */
    public function scopeWithOutstandingUdhar($query)
    {
        return $query->where('udhar_amount', '>', 0);
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
     * Check if sale can be edited
     */
    public function canBeEdited(): bool
    {
        return $this->isDraft();
    }

    /**
     * Check if sale can be confirmed
     */
    public function canBeConfirmed(): bool
    {
        return $this->isDraft() && $this->items()->count() > 0;
    }

    /**
     * Check if sale can be cancelled
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
     * Get payment status label for display
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        $status = $this->getRawOriginal('payment_status') ?? $this->calculatePaymentStatus();
        return match($status) {
            self::PAYMENT_STATUS_PAID => 'Paid',
            self::PAYMENT_STATUS_PARTIAL => 'Partial',
            self::PAYMENT_STATUS_UNPAID => 'Unpaid',
            default => ucfirst($status),
        };
    }

    /**
     * Get total cost of goods sold for this sale
     * Calculates from sale items cost_price
     */
    public function getTotalCOGSAttribute(): float
    {
        return $this->items->reduce(function($carry, $item) {
            return $carry + $item->COGS;
        }, 0);
    }

    /**
     * Get net revenue for this sale (after returns)
     */
    public function getNetRevenueAttribute(): float
    {
        return $this->items->reduce(function($carry, $item) {
            return $carry + $item->net_revenue;
        }, 0);
    }

    /**
     * Get gross profit for this sale
     * Formula: Net Revenue - COGS
     */
    public function getGrossProfitAttribute(): float
    {
        return $this->net_revenue - $this->total_cogs;
    }

    /**
     * Get profit margin percentage
     * Formula: (Gross Profit / Net Revenue) × 100
     */
    public function getProfitMarginPercentageAttribute(): float
    {
        if ($this->net_revenue == 0) {
            return 0;
        }
        return ($this->gross_profit / $this->net_revenue) * 100;
    }

    /**
     * Get profit status: 'profit', 'loss', or 'break-even'
     */
    public function getProfitStatusAttribute(): string
    {
        $profit = $this->gross_profit;
        if ($profit > 0) {
            return 'profit';
        } elseif ($profit < 0) {
            return 'loss';
        } else {
            return 'break-even';
        }
    }

    /**
     * Check if cost data is available for profit calculation
     */
    /**
     * Check if sale has cost data for all items
     */
    public function getHasCostDataAttribute(): bool
    {
        if ($this->items->isEmpty()) {
            return false;
        }
        return $this->items->whereNotNull('cost_price')->count() === $this->items->count();
    }

    /**
     * Scope to filter sales with profit
     */
    public function scopeWithProfit($query)
    {
        return $query->with(['items' => function ($query) {
            $query->whereNotNull('cost_price');
        }]);
    }

    /**
     * Scope for user warehouse authorization
     */
    public function scopeForUserWarehouses($query, $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        $warehouseIds = $user->warehouses()->pluck('warehouses.id');
        return $query->whereIn('warehouse_id', $warehouseIds);
    }
}
