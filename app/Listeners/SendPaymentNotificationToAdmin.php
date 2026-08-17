<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use App\Mail\Customers\PaymentReceiptMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentNotificationToAdmin implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(PaymentReceived $event): void
    {
        $payment = $event->payment;
        
        // Get admin email
        $adminEmail = config('mail.admin_email', 'admin@example.com');
        
        // Only send if email exists and is valid
        if (empty($adminEmail) || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            Log::info("Payment notification admin email not sent - no valid admin email for payment {$payment->id}");
            return;
        }

        try {
            Mail::to($adminEmail)->send(new PaymentReceiptMail($payment));
            Log::info("Payment notification admin email sent successfully", [
                'payment_id' => $payment->id,
                'sale_id' => $payment->sale_id,
                'amount' => $payment->amount,
                'admin_email' => $adminEmail,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send payment notification admin email", [
                'payment_id' => $payment->id,
                'sale_id' => $payment->sale_id,
                'amount' => $payment->amount,
                'admin_email' => $adminEmail,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
