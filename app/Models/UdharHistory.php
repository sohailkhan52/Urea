<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $customer_id
 * @property int $sale_id
 * @property int|null $payment_id
 * @property string $transaction_type
 * @property float $previous_total_amount
 * @property float $current_total_amount
 * @property float $previous_paid_amount
 * @property float $current_paid_amount
 * @property float $previous_udhar_amount
 * @property float $current_udhar_amount
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
class UdharHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * Transaction type constants
     */
    public const TYPE_SALE_CREATED = 'sale_created';
    public const TYPE_PAYMENT_RECEIVED = 'payment_received';
    public const TYPE_PAYMENT_ADJUSTED = 'payment_adjusted';
    public const TYPE_SALE_MODIFIED = 'sale_modified';
    public const TYPE_SALE_CANCELLED = 'sale_cancelled';

    /**
     * Available transaction types
     */
    public static array $types = [
        self::TYPE_SALE_CREATED => 'Sale Created',
        self::TYPE_PAYMENT_RECEIVED => 'Payment Received',
        self::TYPE_PAYMENT_ADJUSTED => 'Payment Adjusted',
        self::TYPE_SALE_MODIFIED => 'Sale Modified',
        self::TYPE_SALE_CANCELLED => 'Sale Cancelled',
    ];

    /**
     * Account type constants
     */
    public const ACCOUNT_TYPE_INDIVIDUAL = 'individual';
    public const ACCOUNT_TYPE_FAMILY = 'family';

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

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'sale_id',
        'payment_id',
        'account_type',
        'account_family_id',
        'transaction_type',
        'previous_total_amount',
        'current_total_amount',
        'previous_paid_amount',
        'current_paid_amount',
        'previous_udhar_amount',
        'current_udhar_amount',
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
            'previous_udhar_amount' => 'decimal:2',
            'current_udhar_amount' => 'decimal:2',
            'amount_changed' => 'decimal:2',
            'transaction_date' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the customer this history record belongs to
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the sale this history record is for
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class)->withTrashed();
    }

    /**
     * Get the payment (if applicable)
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class)->withTrashed();
    }

    /**
     * Get the user who created this history entry
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the account family (if family account)
     */
    public function accountFamily()
    {
        return $this->belongsTo(Family::class, 'account_family_id');
    }

    /**
     * Scope to filter by transaction type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope to filter by account type
     */
    public function scopeByAccountType($query, $accountType)
    {
        return $query->where('account_type', $accountType);
    }

    /**
     * Scope to filter by family account
     */
    public function scopeByFamilyAccount($query, $familyId)
    {
        return $query->where('account_type', self::ACCOUNT_TYPE_FAMILY)
                     ->where('account_family_id', $familyId);
    }

    /**
     * Scope for individual account transactions
     */
    public function scopeIndividualAccount($query)
    {
        return $query->where('account_type', self::ACCOUNT_TYPE_INDIVIDUAL);
    }

    /**
     * Scope for family account transactions
     */
    public function scopeFamilyAccount($query)
    {
        return $query->where('account_type', self::ACCOUNT_TYPE_FAMILY);
    }

    /**
     * Scope to filter by customer
     */
    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope to filter by sale
     */
    public function scopeBySale($query, $saleId)
    {
        return $query->where('sale_id', $saleId);
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
            self::TYPE_SALE_CREATED => 'danger',
            self::TYPE_PAYMENT_RECEIVED => 'success',
            self::TYPE_PAYMENT_ADJUSTED => 'info',
            self::TYPE_SALE_MODIFIED => 'warning',
            self::TYPE_SALE_CANCELLED => 'secondary',
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
     * Check if amount increased (udhar increased - negative is good for customer)
     */
    public function isUdharIncreased(): bool
    {
        return $this->current_udhar_amount > $this->previous_udhar_amount;
    }

    /**
     * Check if amount decreased (udhar decreased - good for business)
     */
    public function isUdharDecreased(): bool
    {
        return $this->current_udhar_amount < $this->previous_udhar_amount;
    }

    /**
     * Get udhar change amount (positive = increased, negative = decreased)
     */
    public function getUdharChangeAttribute(): float
    {
        return (float) ($this->current_udhar_amount - $this->previous_udhar_amount);
    }

    /**
     * Format amount for display
     */
    public function formatAmount(float $amount): string
    {
        return 'Rs. ' . number_format($amount, 2);
    }
}
