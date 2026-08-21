<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $supplier_id
 * @property string $type
 * @property int|null $purchase_id
 * @property int|null $purchase_payment_id
 * @property float $payable_added
 * @property float $payment_made
 * @property float $balance
 * @property string|null $description
 * @property string|null $reference_number
 * @property \Illuminate\Support\Carbon $date
 * @property int $created_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class SupplierLedger extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * Ledger entry types
     */
    public const TYPE_OPENING_BALANCE = 'opening_balance';
    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_ADJUSTMENT = 'adjustment';

    /**
     * Available types
     */
    public static array $types = [
        self::TYPE_OPENING_BALANCE => 'Opening Balance',
        self::TYPE_PURCHASE => 'Purchase',
        self::TYPE_PAYMENT => 'Payment',
        self::TYPE_ADJUSTMENT => 'Adjustment',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'supplier_id',
        'type',
        'purchase_id',
        'purchase_payment_id',
        'payable_added',
        'payment_made',
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
            'payable_added' => 'decimal:2',
            'payment_made' => 'decimal:2',
            'balance' => 'decimal:2',
            'date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Relationships
     */

    /**
     * Get the supplier this ledger belongs to
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the purchase that created this entry (if any)
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get the payment that created this entry (if any)
     */
    public function purchasePayment()
    {
        return $this->belongsTo(PurchasePayment::class);
    }

    /**
     * Get the user who created this entry
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
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
            self::TYPE_OPENING_BALANCE => 'secondary',
            self::TYPE_PURCHASE => 'warning',
            self::TYPE_PAYMENT => 'success',
            self::TYPE_ADJUSTMENT => 'info',
            default => 'secondary',
        };
    }
}
