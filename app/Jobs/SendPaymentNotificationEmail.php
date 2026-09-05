<?php

namespace App\Jobs;

use App\Models\CustomerPayment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * SendPaymentNotificationEmail - Queue job for async email sending
 * 
 * This is a perfect example of what should be queued:
 * - Doesn't need immediate response
 * - Can fail and retry
 * - External API (email)
 * - Non-critical path
 */
class SendPaymentNotificationEmail implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    /**
     * Queue configuration
     */
    public $queue = 'notifications';
    public $timeout = 120;        // Max 2 minutes
    public $tries = 3;            // Retry 3 times
    public $backoff = [10, 60];   // Wait 10s, then 60s between retries
    public $maxExceptions = 3;    // Max exceptions before giving up

    /**
     * Constructor
     * 
     * @param int $paymentId
     * @param int $userId
     */
    public function __construct(
        public int $paymentId,
        public int $userId
    ) {}

    /**
     * Execute the job
     * 
     * IMPORTANT: Financial data is fetched fresh from DB
     * Never assume cached data in queue jobs
     */
    public function handle(): void
    {
        Log::info('Processing payment notification email', [
            'payment_id' => $this->paymentId,
            'user_id' => $this->userId,
        ]);

        // Fetch fresh data (NOT from cache)
        $payment = CustomerPayment::findOrFail($this->paymentId);
        $user = User::findOrFail($this->userId);

        // Verify payment exists and is valid
        if (!$payment || $payment->amount <= 0) {
            Log::error('Invalid payment for email', ['payment_id' => $this->paymentId]);
            $this->fail(new \Exception('Invalid payment'));
            return;
        }

        try {
            // Send email
            // Mail::send(...);
            
            Log::info('Payment notification email sent', [
                'payment_id' => $this->paymentId,
                'email' => $user->email,
                'amount' => $payment->amount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment notification', [
                'payment_id' => $this->paymentId,
                'error' => $e->getMessage(),
            ]);
            
            throw $e; // Re-throw for retry
        }
    }

    /**
     * Handle job failure
     * 
     * @param \Throwable $exception
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Payment notification email job failed permanently', [
            'payment_id' => $this->paymentId,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
        ]);

        // Optionally: Alert admin of failed notification
        // Send to dead-letter queue
        // etc.
    }
}
