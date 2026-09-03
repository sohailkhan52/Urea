<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Family Model
 * 
 * Represents a family grouping for customers.
 * Multiple customers can belong to the same family.
 * 
 * @property int $id
 * @property string $family_code
 * @property string $name
 * @property string|null $address
 * @property string|null $city
 * @property string|null $village
 * @property string|null $notes
 * @property string $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Family extends Model
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
        'family_code',
        'name',
        'address',
        'city',
        'village',
        'notes',
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
     * Check if family is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if family is inactive
     */
    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    /**
     * Get all customers belonging to this family
     */
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Get all sales for this family
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Scope to filter active families
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to filter inactive families
     */
    public function scopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            default => 'Unknown',
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_INACTIVE => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Get total members count
     */
    public function getTotalMembersAttribute(): int
    {
        return $this->customers()->count();
    }

    /**
     * Get total sales count for this family
     */
    public function getTotalSalesAttribute(): int
    {
        return $this->sales()->count();
    }

    /**
     * Generate next family code
     * 
     * @return string
     */
    public static function generateFamilyCode(): string
    {
        $lastFamily = self::withTrashed()->orderBy('id', 'desc')->first();
        
        if (!$lastFamily) {
            return 'FAM-0001';
        }

        // Extract number from last code (e.g., FAM-0001 → 1)
        preg_match('/FAM-(\d+)/', $lastFamily->family_code, $matches);
        $lastNumber = isset($matches[1]) ? (int)$matches[1] : 0;
        
        $nextNumber = $lastNumber + 1;
        
        return sprintf('FAM-%04d', $nextNumber);
    }
}
