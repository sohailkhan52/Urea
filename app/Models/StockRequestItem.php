<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * StockRequestItem Model
 * 
 * Represents individual products in a stock request.
 * 
 * @property int $id
 * @property int $stock_request_id
 * @property int $product_id
 * @property float $requested_quantity
 * @property float $approved_quantity
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class StockRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_request_id',
        'product_id',
        'requested_quantity',
        'approved_quantity',
        'notes',
    ];

    protected $casts = [
        'requested_quantity' => 'decimal:2',
        'approved_quantity' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the stock request this item belongs to
     */
    public function stockRequest(): BelongsTo
    {
        return $this->belongsTo(StockRequest::class);
    }

    /**
     * Get the product for this item
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if item is fully approved
     */
    public function isFullyApproved(): bool
    {
        return $this->approved_quantity >= $this->requested_quantity;
    }

    /**
     * Check if item is partially approved
     */
    public function isPartiallyApproved(): bool
    {
        return $this->approved_quantity > 0 && $this->approved_quantity < $this->requested_quantity;
    }

    /**
     * Check if item is not approved
     */
    public function isNotApproved(): bool
    {
        return $this->approved_quantity == 0;
    }

    /**
     * Get the difference between requested and approved
     */
    public function getPendingQuantityAttribute(): float
    {
        return max(0, $this->requested_quantity - $this->approved_quantity);
    }

    /**
     * Get approval percentage
     */
    public function getApprovalPercentageAttribute(): float
    {
        if ($this->requested_quantity == 0) {
            return 0;
        }

        return ($this->approved_quantity / $this->requested_quantity) * 100;
    }

    /**
     * Get subtotal (for display purposes, can be enhanced with pricing later)
     */
    public function getSubtotalAttribute(): float
    {
        return $this->requested_quantity * ($this->product->sale_price ?? 0);
    }

    /**
     * Get approved subtotal
     */
    public function getApprovedSubtotalAttribute(): float
    {
        return $this->approved_quantity * ($this->product->sale_price ?? 0);
    }
}
