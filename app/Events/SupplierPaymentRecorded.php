<?php

namespace App\Events;

use App\Models\PurchasePayment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a supplier payment is successfully recorded
 * 
 * Fired after:
 * - PurchasePayment record created
 * - Purchase paid_amount updated
 * - Ledger entry created
 */
class SupplierPaymentRecorded
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public PurchasePayment $payment
    ) {}
}
