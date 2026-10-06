<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * ProductUnit Model - Relationship between products and their available units
 * 
 * This model represents:
 * - Which units can be used for a product (KG, Bag, Dozen, etc.)
 * - Conversion factors to the base unit
 * - Per-unit pricing (overrides product base price if set)
 * - Per-unit barcodes
 * 
 * @property int $id
 * @property int $product_id
 * @property int $unit_id
 * @property float $conversion_to_base
 * @property float|null $purchase_price
 * @property float|null $sale_price
 * @property string|null $barcode
 * @property bool $is_active
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class ProductUnit extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'unit_id',
        'package_name',
        'conversion_to_base',
        'purchase_price',
        'sale_price',
        'barcode',
        'is_active',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'conversion_to_base' => 'decimal:4',
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Validate conversion_to_base on save
        static::saving(function ($productUnit) {
            if ($productUnit->conversion_to_base <= 0) {
                throw new \InvalidArgumentException('Conversion to base must be greater than 0');
            }
        });
    }

    // ========== HELPER METHODS ==========

    /**
     * Check if this is the base unit for the product.
     *
     * A ProductUnit is the base unit only when ALL three conditions are true:
     *   1. Its unit_id matches the product's base_unit_id
     *   2. Its conversion_to_base is exactly 1.0  (no scaling)
     *   3. It has no package_name  (named variants like "carton"/"box" are NOT the base,
     *      even when they share the same unit_id and happen to have conversion 1)
     */
    public function isBaseUnit(): bool
    {
        return (int) $this->unit_id === (int) $this->product->base_unit_id
            && abs((float) $this->conversion_to_base - 1.0) < 0.0001
            && ($this->package_name === null || $this->package_name === '');
    }

    /**
     * Get effective purchase price (unit price or product default)
     */
    public function getEffectivePurchasePriceAttribute(): ?float
    {
        return $this->purchase_price ?? $this->product->purchase_price;
    }

    /**
     * Get effective sale price (unit price or product default)
     */
    public function getEffectiveSalePriceAttribute(): ?float
    {
        return $this->sale_price ?? $this->product->sale_price;
    }

    /**
     * Convert quantity from this unit to base unit
     * 
     * @param float $quantity Quantity in this unit
     * @return float Quantity in base unit
     */
    public function convertToBase(float $quantity): float
    {
        return $quantity * $this->conversion_to_base;
    }

    /**
     * Convert quantity from base unit to this unit
     * 
     * @param float $baseQuantity Quantity in base unit
     * @return float Quantity in this unit
     */
    public function convertFromBase(float $baseQuantity): float
    {
        return $baseQuantity / $this->conversion_to_base;
    }

    /**
     * Get display label with conversion info
     * Example: "Small Carton (10.00 KG)" or "Bag (50.00 KG)"
     */
    public function getDisplayLabelAttribute(): string
    {
        // Use package_name if available, otherwise use unit name
        $displayName = $this->package_name ?: $this->unit->name;
        
        if ($this->isBaseUnit()) {
            return "{$displayName} (Base)";
        }
        
        $baseUnitName = $this->product->baseUnit->abbreviation ?? '';
        $conversion = number_format($this->conversion_to_base, 2);
        
        return "{$displayName} ({$conversion} {$baseUnitName})";
    }
    
    /**
     * Get the display name (package name or unit name)
     * Example: "Small Carton" or "Kilogram"
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->package_name ?: $this->unit->name;
    }

    // ========== SCOPES ==========

    /**
     * Scope to get only active product units
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by product
     */
    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope to filter by unit
     */
    public function scopeForUnit(Builder $query, int $unitId): Builder
    {
        return $query->where('unit_id', $unitId);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Scope to get base unit for a product
     */
    public function scopeBaseUnit(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId)
                     ->where('conversion_to_base', 1);
    }

    // ========== RELATIONSHIPS ==========

    /**
     * Get the product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the unit
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    // ========== VALIDATION RULES ==========

    /**
     * Get validation rules for creating/updating product units
     */
    public static function validationRules(bool $isUpdate = false): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'unit_id' => 'required|exists:units,id',
            'conversion_to_base' => 'required|numeric|min:0.0001|max:999999.9999',
            'purchase_price' => 'nullable|numeric|min:0|max:99999999999.99',
            'sale_price' => 'nullable|numeric|min:0|max:99999999999.99',
            'barcode' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];
    }

    /**
     * Get validation messages
     */
    public static function validationMessages(): array
    {
        return [
            'conversion_to_base.min' => 'Conversion factor must be greater than 0.',
            'conversion_to_base.max' => 'Conversion factor is too large.',
            'unit_id.unique' => 'This unit is already configured for this product.',
        ];
    }
}
