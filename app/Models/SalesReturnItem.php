<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Sales Return Item Model
 * 
 * Represents individual products in a sales return.
 */
class SalesReturnItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sales_return_id',
        'sale_item_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount',
        'total',
        'reason',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Auto-calculate total on save
     */
    protected static function booted(): void
    {
        static::saving(function ($item) {
            $item->total = ($item->quantity * $item->unit_price) - $item->discount;
        });
    }

    /**
     * Get the sales return
     */
    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class);
    }

    /**
     * Get the original sale item
     */
    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }

    /**
     * Get the product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
