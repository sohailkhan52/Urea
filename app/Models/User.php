<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $profile_image
 * @property string $status
 * @property Carbon|null $last_login_at
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'phone', 'profile_image', 'status', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Status constants
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if user is inactive
     */
    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    /**
     * Check if user is suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /**
     * Scope: Get only active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Get profile image URL or default avatar
     */
    public function getProfileImageUrlAttribute(): string
    {
        if ($this->profile_image) {
            return asset('storage/' . $this->profile_image);
        }

        // Generate default avatar with initials
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=fff&background=3498db';
    }

    /*
    |--------------------------------------------------------------------------
    | Role & Permission Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the roles for the user.
     */
    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withTimestamps();
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        return $this->roles()->whereIn('slug', $roles)->exists();
    }

    /**
     * Check if user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->roles()->where('is_super_admin', true)->exists();
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        // Super admin has all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Check if any of user's roles have this permission
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionSlug) {
                $query->where('slug', $permissionSlug);
            })
            ->exists();
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        // Super admin has all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        // Super admin has all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all permissions for the user (via roles).
     */
    public function getAllPermissions(): \Illuminate\Support\Collection
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id');
    }

    /**
     * Assign role to user.
     */
    public function assignRole(Role|string $role): void
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        $this->roles()->syncWithoutDetaching($role);
    }

    /**
     * Remove role from user.
     */
    public function removeRole(Role|string $role): void
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        $this->roles()->detach($role);
    }

    /**
     * Sync roles for user.
     */
    public function syncRoles(array $roles): void
    {
        $roleIds = collect($roles)->map(function ($role) {
            if ($role instanceof Role) {
                return $role->id;
            }

            return Role::where('slug', $role)->firstOrFail()->id;
        });

        $this->roles()->sync($roleIds);
    }

    /*
    |--------------------------------------------------------------------------
    | Warehouse Relationships & Access Control
    |--------------------------------------------------------------------------
    */

    /**
     * Get the primary warehouse assigned to this user (if any)
     * Used for non-super-admin users
     */
    public function warehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get all warehouses this user has access to (with active assignments)
     */
    public function warehouses(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'user_warehouse_assignments')
            ->where('revoked_at', null)
            ->withPivot('access_level', 'assigned_at')
            ->withTimestamps();
    }

    /**
     * Get warehouses managed by this user
     */
    public function managedWarehouses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Warehouse::class, 'manager_id');
    }

    /**
     * Check if user has access to a specific warehouse
     */
    public function canAccessWarehouse(Warehouse|int $warehouse): bool
    {
        // Super admin can access all warehouses
        if ($this->isSuperAdmin()) {
            return true;
        }

        $warehouseId = $warehouse instanceof Warehouse ? $warehouse->id : $warehouse;

        // Check if user has explicit warehouse assignment
        return $this->warehouses()
            ->where('warehouse_id', $warehouseId)
            ->exists();
    }

    /**
     * Get user's assigned warehouse for warehouse-specific operations
     * Returns the primary warehouse if user is not a super admin
     */
    public function getAssignedWarehouse(): ?Warehouse
    {
        // Super admin has no single assigned warehouse
        if ($this->isSuperAdmin()) {
            return null;
        }

        // Return primary warehouse_id if set
        if ($this->warehouse_id) {
            return $this->warehouse;
        }

        // Fall back to first assigned warehouse
        return $this->warehouses()->first();
    }

    /**
     * Check if user is restricted to a single warehouse
     */
    public function isWarehouseRestricted(): bool
    {
        // Super admin is not restricted
        if ($this->isSuperAdmin()) {
            return false;
        }

        // Regular admin is restricted to their assigned warehouse
        return true;
    }

    /**
     * Assign user to a warehouse with specific access level
     */
    public function assignToWarehouse(Warehouse|int $warehouse, string $accessLevel = 'manage'): void
    {
        $warehouseId = $warehouse instanceof Warehouse ? $warehouse->id : $warehouse;
        
        $this->warehouses()->attach($warehouseId, [
            'access_level' => $accessLevel,
            'assigned_at' => now(),
        ]);

        // Also set as primary warehouse if not set
        if (!$this->warehouse_id) {
            $this->update(['warehouse_id' => $warehouseId]);
        }
    }

    /**
     * Remove user from a warehouse
     */
    public function removeFromWarehouse(Warehouse|int $warehouse): void
    {
        $warehouseId = $warehouse instanceof Warehouse ? $warehouse->id : $warehouse;
        
        $this->warehouses()->detach($warehouseId);

        // If removing primary warehouse, clear it
        if ($this->warehouse_id === $warehouseId) {
            $this->update(['warehouse_id' => null]);
        }
    }

    /**
     * Get access level for a specific warehouse
     */
    public function getWarehouseAccessLevel(Warehouse|int $warehouse): ?string
    {
        $warehouseId = $warehouse instanceof Warehouse ? $warehouse->id : $warehouse;

        $assignment = $this->warehouses()
            ->where('warehouse_id', $warehouseId)
            ->first();

        return $assignment?->pivot->access_level;
    }

    /**
     * Revoke all warehouse access
     */
    public function revokeAllWarehouseAccess(): void
    {
        $this->warehouses()->update(['revoked_at' => now()]);
        $this->update(['warehouse_id' => null]);
    }

    /**
     * Check if this is an admin for a warehouse
     */
    public function isWarehouseAdmin(Warehouse|int $warehouse = null): bool
    {
        if ($this->isSuperAdmin()) {
            return false;
        }

        if ($warehouse === null) {
            // Check if user is admin for any warehouse
            return $this->warehouses()->exists();
        }

        $warehouseId = $warehouse instanceof Warehouse ? $warehouse->id : $warehouse;
        
        return $this->warehouses()
            ->where('warehouse_id', $warehouseId)
            ->exists();
    }
}
