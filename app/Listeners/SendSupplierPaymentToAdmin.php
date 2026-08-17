<?php

namespace App\Listeners;

use App\Events\SupplierPaymentRecorded;
use App\Mail\Suppliers\SupplierPaymentMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSupplierPaymentToAdmin implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(SupplierPaymentRecorded $event): void
    {
        $supplierPayment = $event->supplierPayment;
        
        // Get admin email
        $adminEmail = config('mail.admin_email', 'admin@example.com');
        
        // Only send if email exists and is valid
        if (empty($adminEmail) || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            Log::info("Supplier payment admin notification not sent - no valid admin email for payment {$supplierPayment->id}");
            return;
        }

        try {
            Mail::to($adminEmail)->send(new SupplierPaymentMail($supplierPayment));
            Log::info("Supplier payment admin notification sent successfully", [
                'payment_id' => $supplierPayment->id,
                'supplier_name' => $supplierPayment->supplier?->name,
                'amount' => $supplierPayment->amount,
                'admin_email' => $adminEmail,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send supplier payment admin notification", [
                'payment_id' => $supplierPayment->id,
                'supplier_name' => $supplierPayment->supplier?->name,
                'amount' => $supplierPayment->amount,
                'admin_email' => $adminEmail,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
