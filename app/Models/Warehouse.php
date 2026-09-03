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
        'is_default',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Boot the model and register event listeners.
     */
    protected static function booted(): void
    {
        // Clear multi-warehouse feature cache when warehouse status changes
        static::saved(function ($warehouse) {
            // Clear cache if status changed or warehouse was created
            if ($warehouse->wasRecentlyCreated || $warehouse->wasChanged('status')) {
                app(\App\Services\MultiWarehouseFeatureService::class)->clearCache();
            }
        });

        // Clear cache when warehouse is deleted
        static::deleted(function ($warehouse) {
            app(\App\Services\MultiWarehouseFeatureService::class)->clearCache();
        });

        // Clear cache when warehouse is restored (from soft delete)
        static::restored(function ($warehouse) {
            app(\App\Services\MultiWarehouseFeatureService::class)->clearCache();
        });
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

    /*
    |--------------------------------------------------------------------------
    | User & Access Control Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the manager/admin of this warehouse
     */
    public function manager(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get all users assigned to this warehouse (with access)
     */
    public function admins(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_warehouse_assignments')
            ->where('revoked_at', null)
            ->withPivot('access_level', 'assigned_at')
            ->withTimestamps();
    }

    /**
     * Get all users with active access (no revoked dates)
     */
    public function activeAdmins(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->admins();
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
     * Get inventory items for this warehouse
     */
    public function inventory(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WarehouseInventory::class);
    }

    /**
     * Get purchases for this warehouse
     */
    public function purchases(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get sales for this warehouse
     */
    public function sales(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get stock movements for this warehouse
     */
    public function stockMovements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StockMovement::class);
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

    /**
     * Set this warehouse as default
     */
    public function setAsDefault(): bool
    {
        // Remove default from all other warehouses
        static::where('id', '!=', $this->id)->update(['is_default' => false]);
        
        // Set this as default
        return $this->update(['is_default' => true]);
    }

    /**
     * Get the default warehouse
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->where('status', self::STATUS_ACTIVE)->first();
    }

    /**
     * Scope to get only default warehouse
     */
    public function scopeIsDefault($query)
    {
        return $query->where('is_default', true);
    }
}
