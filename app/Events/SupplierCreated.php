<?php

namespace App\Events;

use App\Models\Supplier;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a new supplier is created
 * 
 * Fired after:
 * - Supplier record created and saved
 */
class SupplierCreated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Supplier $supplier
    ) {}
}
