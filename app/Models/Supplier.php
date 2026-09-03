<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

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

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    /**
     * Get all purchases for this supplier
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get all purchase payments for this supplier
     */
    public function purchasePayments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    /**
     * Get all purchase returns for this supplier
     */
    public function purchaseReturns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    /**
     * Get supplier ledger entries
     */
    public function ledgerEntries()
    {
        return $this->hasMany(SupplierLedger::class);
    }

    /**
     * Get payable history for this supplier
     */
    public function payableHistory()
    {
        return $this->hasMany(PayableHistory::class);
    }

    /**
     * Scope to filter active suppliers
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to filter by name or company name
     */
    public function scopeSearch($query, $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('company_name', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    /**
     * Get total outstanding payable for this supplier
     */
    public function getOutstandingPayableAttribute()
    {
        $latestEntry = $this->ledgerEntries()
            ->latest('date')
            ->latest('created_at')
            ->first();

        return $latestEntry ? (float) $latestEntry->balance : 0.0;
    }

    /**
     * Get total purchase amount for this supplier
     */
    public function getTotalPurchaseAmount()
    {
        return $this->purchases()
            ->where('status', Purchase::STATUS_CONFIRMED)
            ->sum('total_amount');
    }

    /**
     * Get total paid amount for this supplier
     */
    public function getTotalPaidAmount()
    {
        return $this->purchasePayments()
            ->sum('amount');
    }
}
