<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $payment_number
 * @property int $supplier_id
 * @property int $purchase_id
 * @property float $amount
 * @property string $payment_method
 * @property string $payment_date
 * @property string|null $reference_number
 * @property string|null $notes
 * @property int $recorded_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class PurchasePayment extends Model
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
        'supplier_id',
        'purchase_id',
        'amount',
        'payment_method',
        'payment_date',
        'reference_number',
        'notes',
        'recorded_by',
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
     * Relationships
     */

    /**
     * Get the supplier that this payment is for
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the purchase this payment is for
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get the user who recorded this payment
     */
    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get ledger entries for this payment
     */
    public function ledgerEntries()
    {
        return $this->hasMany(SupplierLedger::class, 'purchase_payment_id');
    }

    /**
     * Get method label
     */
    public function getMethodLabelAttribute(): string
    {
        return self::$methods[$this->payment_method] ?? 'Unknown';
    }
}
