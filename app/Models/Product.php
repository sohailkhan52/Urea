<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $company_id
 * @property int $category_id
 * @property string $name
 * @property string $sku
 * @property string|null $barcode
 * @property float $bag_weight
 * @property string $weight_unit
 * @property float $purchase_price
 * @property float $sale_price
 * @property int $minimum_stock_level
 * @property string|null $description
 * @property string|null $image
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Status constants
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /**
     * Weight unit constants
     */
    public const UNIT_KG = 'KG';
    public const UNIT_LB = 'LB';
    public const UNIT_TON = 'TON';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'category_id',
        'name',
        'sku',
        'barcode',
        'bag_weight',
        'weight_unit',
        'purchase_price',
        'sale_price',
        'minimum_stock_level',
        'description',
        'image',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bag_weight' => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'minimum_stock_level' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Check if product is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if product is inactive
     */
    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    /**
     * Get the company that owns the product
     */
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the category that owns the product
     */
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get image URL or default image
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }

        // Default product image
        return asset('images/default-product.png');
    }

    /**
     * Get formatted price
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
     * Get weight display (e.g., "50 KG")
     */
    public function getWeightDisplayAttribute(): string
    {
        return number_format($this->bag_weight, 2) . ' ' . $this->weight_unit;
    }

    /**
     * Scope to filter only active products
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to filter only inactive products
     */
    public function scopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    /**
     * Scope to filter by company
     */
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to filter by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Check if product can be deleted
     */
    public function canBeDeleted(): bool
    {
        // TODO: Add checks for warehouse inventory, purchases, sales when those modules are implemented
        // Example:
        // return !$this->warehouseInventory()->exists() 
        //     && !$this->purchaseItems()->exists() 
        //     && !$this->saleItems()->exists();
        
        return true; // For now, always return true
    }

    /**
     * Get available weight units
     */
    public static function getWeightUnits(): array
    {
        return [
            self::UNIT_KG => 'Kilograms (KG)',
            self::UNIT_LB => 'Pounds (LB)',
            self::UNIT_TON => 'Tons (TON)',
        ];
    }

    /**
     * Get warehouse inventory for this product
     */
    public function warehouseInventory(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WarehouseInventory::class);
    }

    /**
     * Calculate current stock across all warehouses (placeholder for future)
     * 
     * Note: Stock is NOT stored in products table.
     * Stock will be calculated from warehouse_inventory table when that module is implemented.
     */
    public function getCurrentStock(): int
    {
        // Now implemented with warehouse_inventory table
        return $this->warehouseInventory()->sum('quantity');
    }

    /**
     * Check if stock is below minimum level (placeholder for future)
     */
    public function isLowStock(): bool
    {
        return $this->getCurrentStock() < $this->minimum_stock_level;
    }
}
