<?php

namespace App\Listeners;

use App\Events\SaleConfirmed;
use App\Mail\Customers\SaleConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSaleConfirmationToAdmin implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(SaleConfirmed $event): void
    {
        $sale = $event->sale;
        
        // Get admin email - typically the first admin user or configured email
        $adminEmail = config('mail.admin_email', 'admin@example.com');
        
        // Only send if email exists and is valid
        if (empty($adminEmail) || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            Log::info("Sale confirmation admin notification not sent - no valid admin email for sale {$sale->id}");
            return;
        }

        try {
            Mail::to($adminEmail)->send(new SaleConfirmationMail($sale));
            Log::info("Sale confirmation admin notification sent successfully", [
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'admin_email' => $adminEmail,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send sale confirmation admin notification", [
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'admin_email' => $adminEmail,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
