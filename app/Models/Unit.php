<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Unit Model - Master list of available measurement units
 * 
 * @property int $id
 * @property string $name
 * @property string $abbreviation
 * @property string $category
 * @property bool $is_active
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Unit extends Model
{
    use HasFactory;

    /**
     * Category constants
     */
    public const CATEGORY_WEIGHT = 'weight';
    public const CATEGORY_VOLUME = 'volume';
    public const CATEGORY_COUNT = 'count';
    public const CATEGORY_PACKAGING = 'packaging';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'abbreviation',
        'category',
        'is_active',
        'description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get available categories
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_WEIGHT => 'Weight',
            self::CATEGORY_VOLUME => 'Volume',
            self::CATEGORY_COUNT => 'Count',
            self::CATEGORY_PACKAGING => 'Packaging',
        ];
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute(): string
    {
        return self::getCategories()[$this->category] ?? $this->category;
    }

    /**
     * Get display name with abbreviation
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->abbreviation})";
    }

    // ========== SCOPES ==========

    /**
     * Scope to get only active units
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // ========== RELATIONSHIPS ==========

    /**
     * Get product units that use this unit
     */
    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }

    /**
     * Get products that use this as base unit
     */
    public function productsAsBaseUnit()
    {
        return $this->hasMany(Product::class, 'base_unit_id');
    }

    /**
     * Get purchase items using this unit
     */
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class, 'unit_id');
    }

    /**
     * Get sale items using this unit
     */
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class, 'unit_id');
    }

    /**
     * Get purchase return items using this unit
     */
    public function purchaseReturnItems()
    {
        return $this->hasMany(PurchaseReturnItem::class, 'unit_id');
    }

    /**
     * Get sale return items using this unit
     */
    public function saleReturnItems()
    {
        return $this->hasMany(SaleReturnItem::class, 'unit_id');
    }
}
