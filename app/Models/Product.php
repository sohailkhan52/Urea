<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Product model - matches the modal view requirements
 * 
 * @property int $id
 * @property string $name
 * @property string|null $sku
 * @property string $unit
 * @property float $purchase_price
 * @property float $sale_price
 * @property string $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Product extends Model
{
    use HasFactory;

    /**
     * Unit constants matching the modal dropdown
     */
    public const UNIT_KG = 'KG';
    public const UNIT_MG = 'MG';
    public const UNIT_PIECE = 'Piece';

    /**
     * The attributes that are mass assignable - modal form fields + system fields
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'sku',
        'unit',
        'purchase_price',
        'sale_price',
        'status',
    ];

    /**
     * The attributes that should have default values
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
        ];
    }

    /**
     * Get available units - matching modal dropdown options
     */
    public static function getUnits(): array
    {
        return [
            self::UNIT_KG => 'Kilogram (KG)',
            self::UNIT_MG => 'Milligram (MG)',
            self::UNIT_PIECE => 'Piece',
        ];
    }

    /**
     * Get formatted sale price
     */
    public function getFormattedSalePriceAttribute(): string
    {
        return 'Rs. ' . number_format($this->sale_price, 2);
    }

    /**
     * Get formatted purchase price
     */
    public function getFormattedPurchasePriceAttribute(): string
    {
        return 'Rs. ' . number_format($this->purchase_price, 2);
    }

    /**
     * Get profit margin
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->purchase_price == 0) {
            return 0;
        }

        return (($this->sale_price - $this->purchase_price) / $this->purchase_price) * 100;
    }

    /**
     * Get product display name with unit
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->unit})";
    }

    // ========== SCOPES ==========

    /**
     * Scope to get only active products
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ========== RELATIONSHIPS ==========

    /**
     * Get purchase items for this product
     */
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Get sale items for this product
     */
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Get stock movements for this product
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
