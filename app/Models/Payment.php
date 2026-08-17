<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $payment_number
 * @property int $customer_id
 * @property int|null $sale_id
 * @property float $amount
 * @property string $payment_method
 * @property string|null $payment_type
 * @property string|null $payment_status
 * @property \Illuminate\Support\Carbon $payment_date
 * @property string|null $reference_number
 * @property string|null $notes
 * @property int $received_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Payment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Payment method constants
     */
    public const METHOD_CASH = 'cash';
    public const METHOD_BANK_TRANSFER = 'bank_transfer';
    public const METHOD_EASYPAISA = 'easypaisa';
    public const METHOD_JAZZ_CASH = 'jazz_cash';
    public const METHOD_CHEQUE = 'cheque';
    public const METHOD_OTHER = 'other';

    /**
     * Payment type constants
     */
    public const TYPE_AGAINST_SALE = 'against_sale';
    public const TYPE_UDHAR_SETTLEMENT = 'udhar_settlement';
    public const TYPE_GENERAL = 'general';

    /**
     * Payment status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Available payment methods
     */
    public static array $methods = [
        self::METHOD_CASH => 'Cash',
        self::METHOD_BANK_TRANSFER => 'Bank Transfer',
        self::METHOD_EASYPAISA => 'Easypaisa',
        self::METHOD_JAZZ_CASH => 'JazzCash',
        self::METHOD_CHEQUE => 'Cheque',
        self::METHOD_OTHER => 'Other',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payment_number',
        'customer_id',
        'sale_id',
        'amount',
        'payment_method',
        'payment_type',
        'payment_status',
        'payment_date',
        'reference_number',
        'notes',
        'received_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the customer this payment is from
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the sale that this payment is for (nullable)
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class)->withTrashed();
    }

    /**
     * Get user who received payment
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get ledger entries created for this payment
     */
    public function ledgerEntries()
    {
        return $this->hasMany(CustomerLedger::class);
    }

    /**
     * Scope: Filter by payment type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('payment_type', $type);
    }

    /**
     * Scope: Filter by payment status
     */
    public function scopeByPaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope: Filter by payment method
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
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
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Check if payment is received
     */
    public function isReceived(): bool
    {
        return $this->payment_status === self::STATUS_RECEIVED;
    }

    /**
     * Check if payment is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->payment_status === self::STATUS_CANCELLED;
    }

    /**
     * Check if payment is against a specific sale
     */
    public function isAgainstSale(): bool
    {
        return $this->payment_type === self::TYPE_AGAINST_SALE;
    }

    /**
     * Get payment method label
     */
    public function getMethodLabelAttribute(): string
    {
        return self::$methods[$this->payment_method] ?? 'Unknown';
    }

    /**
     * Get payment method badge class
     */
    public function getMethodBadgeAttribute(): string
    {
        return match($this->payment_method) {
            self::METHOD_CASH => 'success',
            self::METHOD_BANK_TRANSFER => 'info',
            self::METHOD_EASYPAISA => 'warning',
            self::METHOD_JAZZ_CASH => 'warning',
            self::METHOD_CHEQUE => 'secondary',
            self::METHOD_OTHER => 'light',
            default => 'secondary',
        };
    }

    /**
     * Get payment type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->payment_type) {
            self::TYPE_AGAINST_SALE => 'Against Sale',
            self::TYPE_UDHAR_SETTLEMENT => 'Udhar Settlement',
            self::TYPE_GENERAL => 'General Payment',
            default => 'Unknown',
        };
    }
}
