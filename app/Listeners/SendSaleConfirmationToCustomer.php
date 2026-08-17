<?php

namespace App\Listeners;

use App\Events\SaleConfirmed;
use App\Mail\Customers\SaleConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSaleConfirmationToCustomer implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(SaleConfirmed $event): void
    {
        $sale = $event->sale;
        
        // Get customer email
        $email = $sale->customer?->email ?? $sale->walkin_customer_contact;
        
        // Only send if email exists and is valid
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::info("Sale confirmation email not sent - no valid email for sale {$sale->id}");
            return;
        }

        try {
            Mail::to($email)->send(new SaleConfirmationMail($sale));
            Log::info("Sale confirmation email sent successfully", [
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'recipient' => $email,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send sale confirmation email", [
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'recipient' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
