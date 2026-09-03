<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $sale_id
 * @property int $product_id
 * @property float $quantity
 * @property float $unit_price
 * @property float $discount
 * @property float $total
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class SaleItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount',
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
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the sale
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get return items for this sale item
     */
    public function returnItems()
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    /**
     * Get confirmed return items only
     */
    public function confirmedReturnItems()
    {
        return $this->returnItems()
            ->whereHas('saleReturn', function ($query) {
                $query->where('status', 'confirmed');
            });
    }

    /**
     * Get total returned quantity
     */
    public function getTotalReturnedQuantityAttribute(): float
    {
        return $this->confirmedReturnItems()->sum('quantity');
    }

    /**
     * Get remaining returnable quantity
     */
    public function getReturnableQuantityAttribute(): float
    {
        return max(0, $this->quantity - $this->total_returned_quantity);
    }

    /**
     * Check if item can be returned (has remaining quantity)
     */
    public function canBeReturned(): bool
    {
        return $this->returnable_quantity > 0;
    }

    /**
     * Calculate total on save
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->total = ($item->quantity * $item->unit_price) - $item->discount;
        });
    }
}
