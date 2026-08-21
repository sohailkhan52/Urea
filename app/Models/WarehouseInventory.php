<?php

namespace App\Models;

use App\Traits\WarehouseScopeable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $warehouse_id
 * @property int $product_id
 * @property int $quantity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class WarehouseInventory extends Model
{
    use HasFactory, WarehouseScopeable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'warehouse_inventory';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'warehouse_id',
        'product_id',
        'quantity',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the warehouse that owns the inventory
     */
    public function warehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the product for this inventory
     */
    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Check if stock is available
     */
    public function hasStock(): bool
    {
        return $this->quantity > 0;
    }

    /**
     * Check if stock is below minimum level
     */
    public function isLowStock(): bool
    {
        return $this->quantity < $this->product->minimum_stock_level;
    }

    /**
     * Scope to filter items with stock
     */
    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Scope to filter low stock items
     */
    public function scopeLowStock($query)
    {
        return $query->whereHas('product', function ($q) {
            $q->whereRaw('warehouse_inventory.quantity < products.minimum_stock_level');
        });
    }
}
