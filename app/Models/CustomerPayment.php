<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPayment extends Model
{
    use HasFactory;

    /**
     * Account type constants
     */
    public const ACCOUNT_TYPE_INDIVIDUAL = 'individual';
    public const ACCOUNT_TYPE_FAMILY = 'family';

    protected $fillable = [
        'customer_id',
        'sale_id',
        'account_type',
        'account_family_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'notes',
        'received_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Get the customer that made this payment
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the sale this payment is for
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the user who received this payment
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get the account family (for family payments)
     */
    public function accountFamily(): BelongsTo
    {
        return $this->belongsTo(Family::class, 'account_family_id');
    }

    /**
     * Check if payment is for individual account
     */
    public function isIndividualAccount(): bool
    {
        return $this->account_type === self::ACCOUNT_TYPE_INDIVIDUAL;
    }

    /**
     * Check if payment is for family account
     */
    public function isFamilyAccount(): bool
    {
        return $this->account_type === self::ACCOUNT_TYPE_FAMILY;
    }

    /**
     * Scope to get payments for a specific customer
     */
    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope to get payments for a specific sale
     */
    public function scopeForSale($query, int $saleId)
    {
        return $query->where('sale_id', $saleId);
    }

    /**
     * Scope to get payments within date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Get formatted payment method
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return $this->payment_method ? ucfirst($this->payment_method) : 'Cash';
    }
}
