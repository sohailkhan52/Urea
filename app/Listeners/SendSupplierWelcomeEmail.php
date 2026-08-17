<?php

namespace App\Listeners;

use App\Events\SupplierCreated;
use App\Mail\Suppliers\WelcomeMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSupplierWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(SupplierCreated $event): void
    {
        $supplier = $event->supplier;
        
        // Get supplier email
        $email = $supplier->email;
        
        // Only send if email exists and is valid
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::info("Supplier welcome email not sent - no valid email for supplier {$supplier->id}");
            return;
        }

        try {
            Mail::to($email)->send(new WelcomeMail($supplier));
            Log::info("Supplier welcome email sent successfully", [
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->name,
                'recipient' => $email,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send supplier welcome email", [
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->name,
                'recipient' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
