<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sale Return Item Model
 * 
 * Represents individual products being returned in a sale return.
 * Each item references the original sale item and tracks the return quantity and amount.
 * 
 * @property int $id
 * @property int $sale_return_id
 * @property int $sale_item_id
 * @property int $product_id
 * @property float $quantity
 * @property float $unit_price
 * @property float $total
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class SaleReturnItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'sale_return_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sale_return_id',
        'sale_item_id',
        'product_id',
        'quantity',
        'unit_price',
        'total',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ========== RELATIONSHIPS ==========

    /**
     * Get the sale return this item belongs to
     */
    public function saleReturn(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class);
    }

    /**
     * Get the original sale item being returned
     */
    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ========== COMPUTED ATTRIBUTES ==========

    /**
     * Get formatted quantity
     */
    public function getFormattedQuantityAttribute(): string
    {
        return number_format($this->quantity, 2);
    }

    /**
     * Get formatted unit price
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return number_format($this->unit_price, 2);
    }

    /**
     * Get formatted total
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 2);
    }

    // ========== HELPER METHODS ==========

    /**
     * Calculate the total amount
     * total = quantity * unit_price
     */
    public function calculateTotal(): float
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Update the total based on quantity and unit price
     */
    public function updateTotal(): void
    {
        $this->total = $this->calculateTotal();
    }

    // ========== MODEL EVENTS ==========

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically calculate total before creating
        static::creating(function ($item) {
            if (!isset($item->total) || $item->total === null) {
                $item->total = $item->calculateTotal();
            }
        });

        // Automatically recalculate total before updating if quantity or unit_price changed
        static::updating(function ($item) {
            if ($item->isDirty(['quantity', 'unit_price'])) {
                $item->total = $item->calculateTotal();
            }
        });

        // Update parent sale return total when item is created/updated/deleted
        static::saved(function ($item) {
            if ($item->saleReturn) {
                $item->saleReturn->recalculateTotalReturnAmount();
            }
        });

        static::deleted(function ($item) {
            if ($item->saleReturn) {
                $item->saleReturn->recalculateTotalReturnAmount();
            }
        });
    }
}
