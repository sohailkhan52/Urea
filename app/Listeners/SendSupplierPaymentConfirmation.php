<?php

namespace App\Listeners;

use App\Events\SupplierPaymentRecorded;
use App\Mail\Suppliers\SupplierPaymentMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSupplierPaymentConfirmation implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(SupplierPaymentRecorded $event): void
    {
        $supplierPayment = $event->supplierPayment;
        
        // Get supplier email
        $email = $supplierPayment->supplier?->email;
        
        // Only send if email exists and is valid
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::info("Supplier payment confirmation email not sent - no valid email for payment {$supplierPayment->id}");
            return;
        }

        try {
            Mail::to($email)->send(new SupplierPaymentMail($supplierPayment));
            Log::info("Supplier payment confirmation email sent successfully", [
                'payment_id' => $supplierPayment->id,
                'supplier_name' => $supplierPayment->supplier?->name,
                'amount' => $supplierPayment->amount,
                'recipient' => $email,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send supplier payment confirmation email", [
                'payment_id' => $supplierPayment->id,
                'supplier_name' => $supplierPayment->supplier?->name,
                'amount' => $supplierPayment->amount,
                'recipient' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
