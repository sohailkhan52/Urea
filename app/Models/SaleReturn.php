<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Sale Return Model
 * 
 * Represents products returned by customers.
 * Returns add stock back to warehouse and adjust customer balances.
 * 
 * @property int $id
 * @property string $return_number
 * @property int $sale_id
 * @property int|null $customer_id
 * @property int|null $family_id
 * @property int $warehouse_id
 * @property \Illuminate\Support\Carbon $return_date
 * @property float $total_return_amount
 * @property string $status
 * @property string|null $reason
 * @property string|null $notes
 * @property int $created_by
 * @property int|null $confirmed_by
 * @property \Illuminate\Support\Carbon|null $confirmed_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class SaleReturn extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'sales_returns';

    /**
     * Status constants
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'return_number',
        'sale_id',
        'customer_id',
        'family_id',
        'warehouse_id',
        'return_date',
        'total_return_amount',
        'status',
        'reason',
        'notes',
        'created_by',
        'confirmed_by',
        'confirmed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'return_date' => 'date',
            'total_return_amount' => 'decimal:2',
            'confirmed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // ========== RELATIONSHIPS ==========

    /**
     * Get the original sale
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class)->withTrashed();
    }

    /**
     * Get the customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    /**
     * Get the family
     */
    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    /**
     * Get the warehouse
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get return items
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    /**
     * Get creator (user who created this return)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get confirmer (user who confirmed this return)
     */
    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // ========== STATUS CHECK METHODS ==========

    /**
     * Check if return is draft
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if return is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if return is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    // ========== PERMISSION METHODS ==========

    /**
     * Check if return can be edited
     */
    public function canBeEdited(): bool
    {
        return $this->isDraft();
    }

    /**
     * Check if return can be confirmed
     */
    public function canBeConfirmed(): bool
    {
        return $this->isDraft() && $this->items()->count() > 0;
    }

    /**
     * Check if return can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return $this->isDraft();
    }

    // ========== QUERY SCOPES ==========

    /**
     * Scope to filter draft returns
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope to filter confirmed returns
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope to filter cancelled returns
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
     * Scope to filter by family
     */
    public function scopeByFamily($query, $familyId)
    {
        return $query->where('family_id', $familyId);
    }

    /**
     * Scope to filter by warehouse
     */
    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
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
        return $query->whereBetween('return_date', [$startDate, $endDate]);
    }

    // ========== COMPUTED ATTRIBUTES ==========

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
     * Get formatted return amount
     */
    public function getFormattedTotalReturnAmountAttribute(): string
    {
        return number_format($this->total_return_amount, 2);
    }

    /**
     * Get formatted return date
     */
    public function getFormattedReturnDateAttribute(): string
    {
        return $this->return_date->format('d M Y');
    }

    // ========== HELPER METHODS ==========

    /**
     * Calculate and update the total return amount from items
     */
    public function calculateTotalReturnAmount(): float
    {
        return $this->items()->sum('total');
    }

    /**
     * Recalculate and save total return amount
     */
    public function recalculateTotalReturnAmount(): void
    {
        $this->total_return_amount = $this->calculateTotalReturnAmount();
        $this->save();
    }

    /**
     * Generate a unique return number
     * Format: RET-YYYYMMDD-####
     */
    public static function generateReturnNumber(): string
    {
        $prefix = 'RET-';
        $date = now()->format('Ymd');
        
        // Get the last return number for today
        $lastReturn = self::where('return_number', 'like', "{$prefix}{$date}-%")
            ->orderBy('return_number', 'desc')
            ->first();

        if ($lastReturn) {
            // Extract the sequence number and increment
            $lastSequence = (int) substr($lastReturn->return_number, -4);
            $sequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }

        return "{$prefix}{$date}-{$sequence}";
    }
}
