<?php

namespace App\Listeners;

use App\Events\PurchaseConfirmed;
use App\Mail\Suppliers\PurchaseOrderMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPurchaseConfirmationToAdmin implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(PurchaseConfirmed $event): void
    {
        $purchase = $event->purchase;
        
        // Get admin email
        $adminEmail = config('mail.admin_email', 'admin@example.com');
        
        // Only send if email exists and is valid
        if (empty($adminEmail) || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            Log::info("Purchase confirmation admin notification not sent - no valid admin email for purchase {$purchase->id}");
            return;
        }

        try {
            Mail::to($adminEmail)->send(new PurchaseOrderMail($purchase));
            Log::info("Purchase confirmation admin notification sent successfully", [
                'purchase_id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'supplier_name' => $purchase->supplier?->name,
                'admin_email' => $adminEmail,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send purchase confirmation admin notification", [
                'purchase_id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'supplier_name' => $purchase->supplier?->name,
                'admin_email' => $adminEmail,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
