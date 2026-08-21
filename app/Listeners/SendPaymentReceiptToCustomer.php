<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use App\Mail\Customers\PaymentReceiptMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentReceiptToCustomer implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(PaymentReceived $event): void
    {
        $payment = $event->payment;
        
        // Get customer email - try multiple sources
        $customer = $payment->sale?->customer;
        $email = $customer?->email ?? $payment->sale?->walkin_customer_contact;
        
        // Only send if email exists and is valid
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::info("Payment receipt email not sent - no valid email for payment {$payment->id}");
            return;
        }

        try {
            Mail::to($email)->send(new PaymentReceiptMail($payment));
            Log::info("Payment receipt email sent successfully", [
                'payment_id' => $payment->id,
                'sale_id' => $payment->sale_id,
                'amount' => $payment->amount,
                'recipient' => $email,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send payment receipt email", [
                'payment_id' => $payment->id,
                'sale_id' => $payment->sale_id,
                'amount' => $payment->amount,
                'recipient' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
