<?php

namespace App\Listeners;

use App\Events\PurchaseConfirmed;
use App\Mail\Suppliers\PurchaseOrderMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPurchaseConfirmationToSupplier implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(PurchaseConfirmed $event): void
    {
        $purchase = $event->purchase;
        
        // Get supplier email
        $email = $purchase->supplier?->email;
        
        // Only send if email exists and is valid
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::info("Purchase confirmation email not sent to supplier - no valid email for purchase {$purchase->id}");
            return;
        }

        try {
            Mail::to($email)->send(new PurchaseOrderMail($purchase));
            Log::info("Purchase confirmation email sent to supplier successfully", [
                'purchase_id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'supplier_name' => $purchase->supplier?->name,
                'recipient' => $email,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send purchase confirmation email to supplier", [
                'purchase_id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'supplier_name' => $purchase->supplier?->name,
                'recipient' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
