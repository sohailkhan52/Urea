<?php

namespace App\Listeners;

use App\Events\CustomerCreated;
use App\Mail\Customers\WelcomeMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCustomerWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(CustomerCreated $event): void
    {
        $customer = $event->customer;
        
        // Get customer email
        $email = $customer->email;
        
        // Only send if email exists and is valid
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::info("Customer welcome email not sent - no valid email for customer {$customer->id}");
            return;
        }

        try {
            Mail::to($email)->send(new WelcomeMail($customer));
            Log::info("Customer welcome email sent successfully", [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'recipient' => $email,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send customer welcome email", [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'recipient' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
