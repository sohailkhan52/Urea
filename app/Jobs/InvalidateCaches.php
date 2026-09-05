<?php

namespace App\Jobs;

use App\Services\CacheService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * InvalidateCaches - Queue job for async cache invalidation
 * 
 * Removes specific caches after data changes
 * Can be batched for efficiency
 */
class InvalidateCaches implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public $queue = 'default';
    public $timeout = 30;
    public $tries = 2;

    /**
     * Constructor
     * 
     * @param array $cacheKeys - Keys to invalidate
     */
    public function __construct(
        public array $cacheKeys
    ) {}

    /**
     * Execute the job
     */
    public function handle(CacheService $cacheService): void
    {
        Log::info('Invalidating caches', ['count' => count($this->cacheKeys)]);

        foreach ($this->cacheKeys as $key) {
            try {
                // Use appropriate invalidation method based on key pattern
                if (str_starts_with($key, 'warehouse:')) {
                    $warehouseId = explode(':', $key)[1];
                    $cacheService->invalidateWarehouse((int)$warehouseId);
                } elseif (str_starts_with($key, 'customers:list:')) {
                    $warehouseId = explode(':', $key)[2];
                    $cacheService->invalidateCustomersList((int)$warehouseId);
                } elseif (str_starts_with($key, 'families:')) {
                    $cacheService->invalidateFamiliesList();
                } else {
                    \Illuminate\Support\Facades\Cache::forget($key);
                }

                Log::debug('Cache key invalidated', ['key' => $key]);
            } catch (\Exception $e) {
                Log::error('Failed to invalidate cache', [
                    'key' => $key,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Cache invalidation completed');
    }
}
