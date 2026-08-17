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
     * Get customer relationship
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get sale relationship (nullable)
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
     * Scope to filter by payment method
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
}
