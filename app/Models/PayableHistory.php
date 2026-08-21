<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $supplier_id
 * @property int $purchase_id
 * @property int|null $payment_id
 * @property string $transaction_type
 * @property float $previous_total_amount
 * @property float $current_total_amount
 * @property float $previous_paid_amount
 * @property float $current_paid_amount
 * @property float $previous_payable_amount
 * @property float $current_payable_amount
 * @property float $amount_changed
 * @property string $description
 * @property string|null $notes
 * @property string|null $payment_method
 * @property string|null $reference_number
 * @property string $status
 * @property int $created_by
 * @property string|null $ip_address
 * @property \Illuminate\Support\Carbon $transaction_date
 * @property \Illuminate\Support\Carbon $created_at
 */
class PayableHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * Transaction type constants
     */
    public const TYPE_PURCHASE_CREATED = 'purchase_created';
    public const TYPE_PAYMENT_RECORDED = 'payment_recorded';
    public const TYPE_PAYMENT_ADJUSTED = 'payment_adjusted';
    public const TYPE_PURCHASE_MODIFIED = 'purchase_modified';
    public const TYPE_PURCHASE_CANCELLED = 'purchase_cancelled';

    /**
     * Available transaction types
     */
    public static array $types = [
        self::TYPE_PURCHASE_CREATED => 'Purchase Created',
        self::TYPE_PAYMENT_RECORDED => 'Payment Recorded',
        self::TYPE_PAYMENT_ADJUSTED => 'Payment Adjusted',
        self::TYPE_PURCHASE_MODIFIED => 'Purchase Modified',
        self::TYPE_PURCHASE_CANCELLED => 'Purchase Cancelled',
    ];

    /**
     * Status constants
     */
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_PENDING = 'pending';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REVERSED = 'reversed';

    /**
     * Available statuses
     */
    public static array $statuses = [
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_PENDING => 'Pending',
        self::STATUS_FAILED => 'Failed',
        self::STATUS_REVERSED => 'Reversed',
    ];

    protected $table = 'payable_histories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'supplier_id',
        'purchase_id',
        'payment_id',
        'transaction_type',
        'previous_total_amount',
        'current_total_amount',
        'previous_paid_amount',
        'current_paid_amount',
        'previous_payable_amount',
        'current_payable_amount',
        'amount_changed',
        'description',
        'notes',
        'payment_method',
        'reference_number',
        'status',
        'created_by',
        'ip_address',
        'transaction_date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'previous_total_amount' => 'decimal:2',
            'current_total_amount' => 'decimal:2',
            'previous_paid_amount' => 'decimal:2',
            'current_paid_amount' => 'decimal:2',
            'previous_payable_amount' => 'decimal:2',
            'current_payable_amount' => 'decimal:2',
            'amount_changed' => 'decimal:2',
            'transaction_date' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the supplier this history record belongs to
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the purchase this history record is for
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class)->withTrashed();
    }

    /**
     * Get the payment (if applicable)
     */
    public function payment()
    {
        return $this->belongsTo(PurchasePayment::class, 'payment_id')->withTrashed();
    }

    /**
     * Get the user who created this history entry
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to filter by transaction type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope to filter by supplier
     */
    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Scope to filter by purchase
     */
    public function scopeByPurchase($query, $purchaseId)
    {
        return $query->where('purchase_id', $purchaseId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get transaction type label
     */
    public function getTypeLabelAttribute(): string
    {
        return self::$types[$this->transaction_type] ?? 'Unknown';
    }

    /**
     * Get transaction type badge class
     */
    public function getTypeBadgeAttribute(): string
    {
        return match($this->transaction_type) {
            self::TYPE_PURCHASE_CREATED => 'danger',
            self::TYPE_PAYMENT_RECORDED => 'success',
            self::TYPE_PAYMENT_ADJUSTED => 'info',
            self::TYPE_PURCHASE_MODIFIED => 'warning',
            self::TYPE_PURCHASE_CANCELLED => 'secondary',
            default => 'light',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::$statuses[$this->status] ?? 'Unknown';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_COMPLETED => 'success',
            self::STATUS_PENDING => 'warning',
            self::STATUS_FAILED => 'danger',
            self::STATUS_REVERSED => 'secondary',
            default => 'light',
        };
    }

    /**
     * Check if payable increased
     */
    public function isPayableIncreased(): bool
    {
        return $this->current_payable_amount > $this->previous_payable_amount;
    }

    /**
     * Check if payable decreased
     */
    public function isPayableDecreased(): bool
    {
        return $this->current_payable_amount < $this->previous_payable_amount;
    }

    /**
     * Get payable change amount (positive = increased, negative = decreased)
     */
    public function getPayableChangeAttribute(): float
    {
        return (float) ($this->current_payable_amount - $this->previous_payable_amount);
    }

    /**
     * Format amount for display
     */
    public function formatAmount(float $amount): string
    {
        return 'Rs. ' . number_format($amount, 2);
    }
}
