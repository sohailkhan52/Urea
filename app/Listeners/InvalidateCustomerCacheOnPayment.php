<?php

namespace App\Listeners;

use App\Events\PaymentRecorded;
use App\Jobs\InvalidateCaches;
use Illuminate\Support\Facades\Log;

/**
 * InvalidateCustomerCacheOnPayment
 * 
 * When a payment is recorded, invalidate related customer/family caches
 * But NEVER invalidate financial balance caches (they're not cached)
 */
class InvalidateCustomerCacheOnPayment
{
    /**
     * Handle the event
     * 
     * @param PaymentRecorded $event
     */
    public function handle(PaymentRecorded $event): void
    {
        $payment = $event->payment;
        
        Log::info('Processing cache invalidation for payment', [
            'payment_id' => $payment->id,
            'customer_id' => $payment->customer_id,
            'family_id' => $payment->account_family_id,
        ]);

        // Collect cache keys to invalidate
        $cacheKeys = [];

        // Invalidate customer list (customer might be removed from lists if all paid)
        $cacheKeys[] = 'customers:list:' . $payment->customer->warehouse_id;

        // Invalidate specific customer cache (not balance, but profile)
        $cacheKeys[] = 'customer:' . $payment->customer_id;

        // If family payment, invalidate family caches
        if ($payment->account_family_id) {
            $cacheKeys[] = 'families:list';
            $cacheKeys[] = 'family:' . $payment->account_family_id . ':members';
            $cacheKeys[] = 'family:' . $payment->account_family_id;
        }

        // Queue cache invalidation asynchronously
        // This keeps payment recording fast
        dispatch(new InvalidateCaches($cacheKeys))->onQueue('default');

        Log::info('Cache invalidation queued', [
            'keys_count' => count($cacheKeys),
            'payment_id' => $payment->id,
        ]);
    }
}
