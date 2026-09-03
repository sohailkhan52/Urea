<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Supplier Ledger - Running balance of supplier payables
 * 
 * This table maintains a complete transaction history for each supplier.
 * Each entry represents a debit (purchase) or credit (payment).
 * The running balance is calculated for accounting and reporting.
 * 
 * @property int $id
 * @property int $supplier_id
 * @property string $type - 'opening_balance', 'purchase', 'payment', 'return', 'adjustment'
 * @property int|null $purchase_id
 * @property int|null $purchase_payment_id
 * @property int|null $purchase_return_id
 * @property float $debit - Amount owed to supplier (purchase)
 * @property float $credit - Amount paid to supplier (payment)
 * @property float $balance - Running balance after this transaction
 * @property string|null $description
 * @property string|null $reference_number
 * @property \Carbon\Carbon $date
 * @property int $created_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class SupplierLedger extends Model
{
    use HasFactory;

    /**
     * Transaction type constants
     */
    public const TYPE_OPENING_BALANCE = 'opening_balance';
    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_RETURN = 'return';
    public const TYPE_ADJUSTMENT = 'adjustment';

    protected $fillable = [
        'supplier_id',
        'type',
        'purchase_id',
        'purchase_payment_id',
        'purchase_return_id',
        'payable_added',
        'payment_made',
        'balance',
        'description',
        'reference_number',
        'date',
        'created_by',
    ];

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
     * Get the supplier for this ledger entry
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the purchase if this is a purchase entry
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get the payment if this is a payment entry
     */
    public function purchasePayment()
    {
        return $this->belongsTo(PurchasePayment::class, 'purchase_payment_id');
    }

    /**
     * Get the purchase return if this is a return entry
     */
    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class, 'purchase_return_id');
    }

    /**
     * Get the user who created this entry
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get transaction type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_OPENING_BALANCE => 'Opening Balance',
            self::TYPE_PURCHASE => 'Purchase',
            self::TYPE_PAYMENT => 'Payment',
            self::TYPE_RETURN => 'Return',
            self::TYPE_ADJUSTMENT => 'Adjustment',
            default => 'Unknown',
        };
    }

    /**
     * Get formatted debit for display
     */
    public function getFormattedDebitAttribute(): string
    {
        return $this->debit > 0 ? 'Rs. ' . number_format($this->debit, 2) : '-';
    }

    /**
     * Get formatted credit for display
     */
    public function getFormattedCreditAttribute(): string
    {
        return $this->credit > 0 ? 'Rs. ' . number_format($this->credit, 2) : '-';
    }

    /**
     * Get formatted balance for display
     */
    public function getFormattedBalanceAttribute(): string
    {
        return 'Rs. ' . number_format($this->balance, 2);
    }
}
