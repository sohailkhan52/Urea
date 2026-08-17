<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string|null $company_name
 * @property string|null $contact_person
 * @property string $phone
 * @property string|null $email
 * @property string|null $address
 * @property string|null $city
 * @property string|null $ntn
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Supplier extends Model
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
        'company_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'city',
        'ntn',
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
     * Boot method to auto-format fields
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($supplier) {
            // Auto-uppercase NTN
            if ($supplier->ntn) {
                $supplier->ntn = strtoupper($supplier->ntn);
            }
        });
    }

    /**
     * Check if supplier is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if supplier is inactive
     */
    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    /**
     * Get purchases from this supplier
     */
    public function purchases(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get payments made to this supplier
     */
    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchasePayment::class);
    }

    /**
     * Get ledger entries for this supplier
     */
    public function ledger(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SupplierLedger::class);
    }

    /**
     * Scope to filter only active suppliers
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to filter only inactive suppliers
     */
    public function scopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    /**
     * Scope to filter by city
     */
    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Check if supplier can be deleted
     */
    public function canBeDeleted(): bool
    {
        // TODO: Add check for purchases when Purchase module is implemented
        // return !$this->purchases()->exists();
        
        return true; // For now, allow deletion
    }

    /**
     * Get full display name
     */
    public function getFullNameAttribute(): string
    {
        if ($this->company_name) {
            return "{$this->name} ({$this->company_name})";
        }
        return $this->name;
    }

    /**
     * Get display name for contact
     */
    public function getContactDisplayAttribute(): string
    {
        $parts = [];
        
        if ($this->contact_person) {
            $parts[] = $this->contact_person;
        }
        
        if ($this->phone) {
            $parts[] = $this->phone;
        }
        
        if ($this->email) {
            $parts[] = $this->email;
        }
        
        return implode(' • ', $parts);
    }

    /**
     * Get all unique cities from suppliers
     */
    public static function getCities(): \Illuminate\Support\Collection
    {
        return self::whereNotNull('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');
    }
}
