<?php

namespace App\Events;

use App\Models\Customer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a new customer is created
 * 
 * Fired after:
 * - Customer record created and saved
 */
class CustomerCreated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Customer $customer
    ) {}
}
