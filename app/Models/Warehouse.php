<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $branch_id
 * @property string $name
 * @property string $code
 * @property string $type
 * @property string $address
 * @property int|null $manager_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Status constants
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /**
     * Type constants
     */
    public const TYPE_MAIN = 'main_warehouse';
    public const TYPE_BRANCH = 'branch_warehouse';
    public const TYPE_STORE = 'store';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'branch_id',
        'name',
        'code',
        'type',
        'address',
        'manager_id',
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
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Check if warehouse is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if warehouse is inactive
     */
    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    /**
     * Check if this is main warehouse
     */
    public function isMainWarehouse(): bool
    {
        return $this->type === self::TYPE_MAIN;
    }

    /**
     * Check if this is branch warehouse
     */
    public function isBranchWarehouse(): bool
    {
        return $this->type === self::TYPE_BRANCH;
    }

    /**
     * Check if this is a store
     */
    public function isStore(): bool
    {
        return $this->type === self::TYPE_STORE;
    }

    /**
     * Get the branch that owns the warehouse
     */
    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the manager (user) for the warehouse
     */
    public function manager(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get inventory items for this warehouse
     */
    public function inventory(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WarehouseInventory::class);
    }

    /**
     * Scope to filter only active warehouses
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to filter only inactive warehouses
     */
    public function scopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    /**
     * Scope to filter by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by branch
     */
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Check if warehouse can be deleted
     */
    public function canBeDeleted(): bool
    {
        // Cannot delete if has inventory
        if ($this->inventory()->where('quantity', '>', 0)->exists()) {
            return false;
        }

        // TODO: Add checks for pending transactions when those modules are implemented
        // Example:
        // return !$this->purchaseOrders()->wherePending()->exists() 
        //     && !$this->transfers()->wherePending()->exists();
        
        return true;
    }

    /**
     * Get total stock value in warehouse (placeholder for future)
     */
    public function getTotalStock(): int
    {
        return $this->inventory()->sum('quantity');
    }

    /**
     * Get total product types in warehouse
     */
    public function getTotalProductTypes(): int
    {
        return $this->inventory()->where('quantity', '>', 0)->count();
    }

    /**
     * Get available warehouse types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_MAIN => 'Main Warehouse',
            self::TYPE_BRANCH => 'Branch Warehouse',
            self::TYPE_STORE => 'Store',
        ];
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return self::getTypes()[$this->type] ?? $this->type;
    }
}
