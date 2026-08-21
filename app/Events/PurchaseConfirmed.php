<?php

namespace App\Events;

use App\Models\Purchase;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a purchase is successfully confirmed
 * 
 * Fired after:
 * - Purchase status changed to 'confirmed'
 * - Stock movements created
 * - Ledger entries created
 */
class PurchaseConfirmed
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Purchase $purchase
    ) {}
}
