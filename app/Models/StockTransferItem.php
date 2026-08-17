<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferItem extends Model
{
    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'quantity',
        'received_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
    ];

    /**
     * Get the transfer
     */
    public function transfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get pending quantity (not yet received)
     */
    public function getPendingQuantityAttribute(): float
    {
        return (float)($this->quantity - $this->received_quantity);
    }

    /**
     * Get completion percentage
     */
    public function getCompletionPercentAttribute(): float
    {
        if ($this->quantity == 0) {
            return 0;
        }
        return ($this->received_quantity / $this->quantity) * 100;
    }
}
