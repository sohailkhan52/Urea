<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $warehouse_id
 * @property string $access_level
 * @property \Illuminate\Support\Carbon $assigned_at
 * @property \Illuminate\Support\Carbon|null $revoked_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class UserWarehouseAssignment extends Model
{
    use HasFactory;

    /**
     * Access level constants
     */
    public const ACCESS_VIEW = 'view';
    public const ACCESS_MANAGE = 'manage';
    public const ACCESS_FULL = 'full';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'warehouse_id',
        'access_level',
        'assigned_at',
        'revoked_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'revoked_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the user for this assignment
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the warehouse for this assignment
     */
    public function warehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Get only active assignments (not revoked)
     */
    public function scopeActive($query)
    {
        return $query->whereNull('revoked_at');
    }

    /**
     * Scope: Get assignments for a specific user
     */
    public function scopeForUser($query, User|int $user)
    {
        $userId = $user instanceof User ? $user->id : $user;
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get assignments for a specific warehouse
     */
    public function scopeForWarehouse($query, Warehouse|int $warehouse)
    {
        $warehouseId = $warehouse instanceof Warehouse ? $warehouse->id : $warehouse;
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope: Get assignments with specific access level
     */
    public function scopeWithAccessLevel($query, string $level)
    {
        return $query->where('access_level', $level);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Check if this assignment is active
     */
    public function isActive(): bool
    {
        return $this->revoked_at === null;
    }

    /**
     * Check if this assignment has been revoked
     */
    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    /**
     * Revoke this assignment
     */
    public function revoke(): void
    {
        $this->update(['revoked_at' => now()]);
    }

    /**
     * Check if user has full access with this assignment
     */
    public function hasFullAccess(): bool
    {
        return $this->access_level === self::ACCESS_FULL;
    }

    /**
     * Check if user can manage with this assignment
     */
    public function canManage(): bool
    {
        return in_array($this->access_level, [self::ACCESS_MANAGE, self::ACCESS_FULL]);
    }

    /**
     * Check if user can view with this assignment
     */
    public function canView(): bool
    {
        return true; // All access levels can view
    }
}
