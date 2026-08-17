<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $logo
 * @property string|null $contact_person
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $website
 * @property string|null $address
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Company extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Status constants
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'logo',
        'contact_person',
        'phone',
        'email',
        'website',
        'address',
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
     * Check if company is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if company is inactive
     */
    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    /**
     * Get logo URL or default logo
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }

        // Generate default logo with company initials
        $initials = $this->getInitials();
        return 'https://ui-avatars.com/api/?name=' . urlencode($initials) . '&color=fff&background=6c757d&size=200';
    }

    /**
     * Get company initials
     */
    public function getInitials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 2
            ? Str::substr($initials, 0, 2)
            : $initials;
    }

    /**
     * Scope to filter only active companies
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to filter only inactive companies
     */
    public function scopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    /**
     * Get products for this company
     */
    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Check if company can be deleted
     * Returns true if company has no associated products or transactions
     */
    public function canBeDeleted(): bool
    {
        // Check if company has products
        if ($this->products()->exists()) {
            return false;
        }

        // TODO: Add checks for transactions when those modules are implemented
        // Example:
        // return !$this->transactions()->exists();
        
        return true;
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
