<?php

namespace App\Events;

use App\Models\Sale;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a sale is successfully confirmed
 * 
 * Fired after:
 * - Sale status changed to 'confirmed'
 * - Stock movements created
 * - Ledger entries created
 */
class SaleConfirmed
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Sale $sale
    ) {}
}
