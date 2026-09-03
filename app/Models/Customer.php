<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $warehouse_id
 * @property string $customer_type
 * @property string $name
 * @property string|null $father_name
 * @property string|null $cnic
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $village
 * @property string|null $city
 * @property string|null $address
 * @property string $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Customer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Customer type constants
     */
    public const TYPE_FARMER = 'farmer';
    public const TYPE_DEALER = 'dealer';
    public const TYPE_RETAIL_CUSTOMER = 'retail_customer';

    /**
     * Status constants
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /**
     * Available customer types
     */
    public static array $types = [
        self::TYPE_FARMER => 'Farmer',
        self::TYPE_DEALER => 'Dealer',
        self::TYPE_RETAIL_CUSTOMER => 'Retail Customer',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'warehouse_id',
        'family_id',
        'customer_type',
        'name',
        'father_name',
        'cnic',
        'phone',
        'email',
        'village',
        'city',
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
     * Check if customer is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if customer is inactive
     */
    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    /**
     * Check if customer is a farmer
     */
    public function isFarmer(): bool
    {
        return $this->customer_type === self::TYPE_FARMER;
    }

    /**
     * Check if customer is a dealer
     */
    public function isDealer(): bool
    {
        return $this->customer_type === self::TYPE_DEALER;
    }

    /**
     * Check if customer is a retail customer
     */
    public function isRetailCustomer(): bool
    {
        return $this->customer_type === self::TYPE_RETAIL_CUSTOMER;
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return self::$types[$this->customer_type] ?? 'Unknown';
    }

    /**
     * Get type badge class
     */
    public function getTypeBadgeAttribute(): string
    {
        return match($this->customer_type) {
            self::TYPE_FARMER => 'primary',
            self::TYPE_DEALER => 'success',
            self::TYPE_RETAIL_CUSTOMER => 'info',
            default => 'secondary',
        };
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
     * Scope to filter active customers
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to filter inactive customers
     */
    public function scopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    /**
     * Scope to filter by customer type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('customer_type', $type);
    }

    /**
     * Scope to filter by city
     */
    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Scope to filter farmers
     */
    public function scopeFarmers($query)
    {
        return $query->where('customer_type', self::TYPE_FARMER);
    }

    /**
     * Scope to filter dealers
     */
    public function scopeDealers($query)
    {
        return $query->where('customer_type', self::TYPE_DEALER);
    }

    /**
     * Scope to filter retail customers
     */
    public function scopeRetailCustomers($query)
    {
        return $query->where('customer_type', self::TYPE_RETAIL_CUSTOMER);
    }

    /**
     * Relationship: Get the warehouse this customer belongs to
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Relationship: Get the family this customer belongs to
     */
    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    /**
     * Relationship: Get all sales for this customer
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Relationship: Get all payments made by this customer
     */
    public function payments()
    {
        return $this->hasMany(CustomerPayment::class);
    }

    /**
     * Get total sales amount for this customer
     */
    public function getTotalSalesAttribute(): float
    {
        return $this->sales()
            ->where('status', Sale::STATUS_CONFIRMED)
            ->sum('total_amount');
    }

    /**
     * Get total paid amount (initial + additional payments)
     */
    public function getTotalPaidAttribute(): float
    {
        // Initial payments at sale time
        $initialPaid = $this->sales()
            ->where('status', Sale::STATUS_CONFIRMED)
            ->sum('paid_amount');
        
        // Additional payments after sale
        $additionalPayments = $this->payments()->sum('amount');
        
        return $initialPaid + $additionalPayments;
    }

    /**
     * Get current udhar (outstanding balance)
     */
    public function getCurrentUdharAttribute(): float
    {
        return max(0, $this->total_sales - $this->total_paid);
    }

    /**
     * Get payment status for customer
     */
    public function getPaymentStatusAttribute(): string
    {
        $totalSales = $this->total_sales;
        $totalPaid = $this->total_paid;

        if ($totalSales == 0) {
            return 'no_sales';
        }

        if ($totalPaid == 0) {
            return 'unpaid';
        }

        if ($totalPaid >= $totalSales) {
            return 'paid';
        }

        return 'partial';
    }

    /**
     * Get payment status badge
     */
    public function getPaymentStatusBadgeAttribute(): string
    {
        return match($this->payment_status) {
            'paid' => 'success',
            'partial' => 'warning',
            'unpaid' => 'danger',
            'no_sales' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get payment status label
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return match($this->payment_status) {
            'paid' => 'Fully Paid',
            'partial' => 'Partially Paid',
            'unpaid' => 'Unpaid',
            'no_sales' => 'No Sales',
            default => 'Unknown',
        };
    }

    /**
     * Relationship: Get all udhar history for this customer
     */
    public function udharHistory()
    {
        return $this->hasMany(UdharHistory::class);
    }

    /**
     * Check if customer can be deleted
     */
    public function canBeDeleted(): bool
    {
        // Can delete if no associated sales/transactions
        // To be implemented when sales module is created
        return true;
    }
}
