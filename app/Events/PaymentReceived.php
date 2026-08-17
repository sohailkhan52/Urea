<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a customer payment is successfully recorded
 * 
 * Fired after:
 * - Payment record created
 * - Sale paid_amount updated
 * - Ledger entry created
 */
class PaymentReceived
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Payment $payment
    ) {}
}
