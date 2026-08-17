<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $customer_id
 * @property string $type
 * @property int|null $sale_id
 * @property int|null $payment_id
 * @property int|null $return_id
 * @property float $debit
 * @property float $credit
 * @property float $balance
 * @property string $description
 * @property string|null $reference_number
 * @property \Illuminate\Support\Carbon $date
 * @property int $created_by
 * @property \Illuminate\Support\Carbon $created_at
 */
class CustomerLedger extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * Ledger entry types
     */
    public const TYPE_OPENING_BALANCE = 'opening_balance';
    public const TYPE_SALE = 'sale';
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_RETURN = 'return';
    public const TYPE_ADJUSTMENT = 'adjustment';

    /**
     * Available types
     */
    public static array $types = [
        self::TYPE_OPENING_BALANCE => 'Opening Balance',
        self::TYPE_SALE => 'Sale',
        self::TYPE_PAYMENT => 'Payment',
        self::TYPE_RETURN => 'Return',
        self::TYPE_ADJUSTMENT => 'Adjustment',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'type',
        'sale_id',
        'payment_id',
        'return_id',
        'debit',
        'credit',
        'balance',
        'description',
        'reference_number',
        'date',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
            'balance' => 'decimal:2',
            'date' => 'date',
            'created_at' => 'datetime',
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
     * Get payment relationship (nullable)
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class)->withTrashed();
    }

    /**
     * Get user who created entry
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to filter by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by customer
     */
    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return self::$types[$this->type] ?? 'Unknown';
    }

    /**
     * Get type badge class
     */
    public function getTypeBadgeAttribute(): string
    {
        return match($this->type) {
            self::TYPE_OPENING_BALANCE => 'light',
            self::TYPE_SALE => 'danger',
            self::TYPE_PAYMENT => 'success',
            self::TYPE_RETURN => 'warning',
            self::TYPE_ADJUSTMENT => 'info',
            default => 'secondary',
        };
    }
}
