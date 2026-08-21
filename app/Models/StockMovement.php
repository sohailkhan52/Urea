<?php

namespace App\Models;

use App\Traits\WarehouseScopeable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $warehouse_id
 * @property int $product_id
 * @property string $type
 * @property string|null $reference_type
 * @property int|null $reference_id
 * @property float $quantity_in
 * @property float $quantity_out
 * @property float $balance_after
 * @property float|null $unit_cost
 * @property string|null $remarks
 * @property int $created_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class StockMovement extends Model
{
    use HasFactory, WarehouseScopeable;

    /**
     * Movement type constants
     */
    public const TYPE_OPENING_STOCK = 'opening_stock';
    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_SALE = 'sale';
    public const TYPE_CUSTOMER_RETURN = 'customer_return';
    public const TYPE_SUPPLIER_RETURN = 'supplier_return';
    public const TYPE_TRANSFER_OUT = 'transfer_out';
    public const TYPE_TRANSFER_IN = 'transfer_in';
    public const TYPE_ADJUSTMENT_IN = 'adjustment_in';
    public const TYPE_ADJUSTMENT_OUT = 'adjustment_out';
    public const TYPE_DAMAGED = 'damaged';
    public const TYPE_EXPIRED = 'expired';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'warehouse_id',
        'product_id',
        'type',
        'reference_type',
        'reference_id',
        'quantity_in',
        'quantity_out',
        'balance_after',
        'unit_cost',
        'remarks',
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
            'quantity_in' => 'decimal:2',
            'quantity_out' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Prevent updates to stock movements (immutable after creation)
     */
    protected static function booted(): void
    {
        static::updating(function ($stockMovement) {
            throw new \Exception('Stock movements cannot be modified after creation. Create a new adjustment instead.');
        });

        static::deleting(function ($stockMovement) {
            throw new \Exception('Stock movements cannot be deleted. They are permanent audit records.');
        });
    }

    /**
     * Get the warehouse for this movement
     */
    public function warehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the product for this movement
     */
    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who created this movement
     */
    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the reference model (polymorphic)
     */
    public function reference(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if this is a stock increase
     */
    public function isStockIn(): bool
    {
        return $this->quantity_in > 0;
    }

    /**
     * Check if this is a stock decrease
     */
    public function isStockOut(): bool
    {
        return $this->quantity_out > 0;
    }

    /**
     * Get the net quantity change
     */
    public function getNetQuantityAttribute(): float
    {
        return $this->quantity_in - $this->quantity_out;
    }

    /**
     * Get all movement types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_OPENING_STOCK => 'Opening Stock',
            self::TYPE_PURCHASE => 'Purchase',
            self::TYPE_SALE => 'Sale',
            self::TYPE_CUSTOMER_RETURN => 'Customer Return',
            self::TYPE_SUPPLIER_RETURN => 'Supplier Return',
            self::TYPE_TRANSFER_OUT => 'Transfer Out',
            self::TYPE_TRANSFER_IN => 'Transfer In',
            self::TYPE_ADJUSTMENT_IN => 'Adjustment In',
            self::TYPE_ADJUSTMENT_OUT => 'Adjustment Out',
            self::TYPE_DAMAGED => 'Damaged',
            self::TYPE_EXPIRED => 'Expired',
        ];
    }

    /**
     * Get movement type label
     */
    public function getTypeLabelAttribute(): string
    {
        return self::getTypes()[$this->type] ?? $this->type;
    }

    /**
     * Scope to filter by warehouse
     */
    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope to filter by product
     */
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope to filter by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter stock in movements
     */
    public function scopeStockIn($query)
    {
        return $query->where('quantity_in', '>', 0);
    }

    /**
     * Scope to filter stock out movements
     */
    public function scopeStockOut($query)
    {
        return $query->where('quantity_out', '>', 0);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
