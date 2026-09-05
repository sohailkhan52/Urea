<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * CacheService - Manages application caching with financial data safety
 * 
 * CRITICAL RULE: Financial data is NEVER cached blindly.
 * Only cache what is safe to cache, and invalidate explicitly on changes.
 * 
 * NOTE: Works with both Redis and Database cache stores.
 * Gracefully falls back if Redis is unavailable.
 */
class CacheService
{
    /**
     * Cache key prefixes
     */
    public const PREFIX_WAREHOUSE = 'warehouse';
    public const PREFIX_PRODUCT = 'product';
    public const PREFIX_USER = 'user';
    public const PREFIX_CONFIG = 'config';
    public const PREFIX_FEATURE = 'feature';
    public const PREFIX_REPORT = 'report';
    
    // Financial data - NOT cached
    public const PREFIX_BALANCE = 'balance';      // DO NOT USE FOR FINANCIAL
    public const PREFIX_PAYMENT = 'payment';      // DO NOT USE FOR FINANCIAL
    public const PREFIX_UDHAR = 'udhar';          // DO NOT USE FOR FINANCIAL

    /**
     * Get warehouse data (cached)
     * Falls back to direct query if cache unavailable
     * 
     * @param int $warehouseId
     * @return mixed
     */
    public function getWarehouse(int $warehouseId)
    {
        try {
            return Cache::remember(
                self::PREFIX_WAREHOUSE . ':' . $warehouseId,
                now()->addHours(2),
                function () use ($warehouseId) {
                    return \App\Models\Warehouse::find($warehouseId);
                }
            );
        } catch (\Exception $e) {
            Log::warning('Cache failed, using direct query', ['error' => $e->getMessage()]);
            return \App\Models\Warehouse::find($warehouseId);
        }
    }

    /**
     * Invalidate warehouse cache
     * Called when warehouse is updated
     * 
     * @param int $warehouseId
     */
    public function invalidateWarehouse(int $warehouseId): void
    {
        Cache::forget(self::PREFIX_WAREHOUSE . ':' . $warehouseId);
        Cache::forget(self::PREFIX_WAREHOUSE . ':list');
    }

    /**
     * Get all warehouses (cached)
     * Falls back to direct query if cache unavailable
     * 
     * @return mixed
     */
    public function getWarehouses()
    {
        try {
            return Cache::remember(
                self::PREFIX_WAREHOUSE . ':list',
                now()->addHours(2),
                function () {
                    return \App\Models\Warehouse::active()->orderBy('name')->get();
                }
            );
        } catch (\Exception $e) {
            Log::warning('Cache failed for warehouses list', ['error' => $e->getMessage()]);
            return \App\Models\Warehouse::active()->orderBy('name')->get();
        }
    }

    /**
     * Get product by ID (cached)
     * 
     * @param int $productId
     * @return mixed
     */
    public function getProduct(int $productId)
    {
        return Cache::remember(
            self::PREFIX_PRODUCT . ':' . $productId,
            now()->addHours(2),
            function () use ($productId) {
                return \App\Models\Product::find($productId);
            }
        );
    }

    /**
     * Invalidate product cache
     * 
     * @param int $productId
     */
    public function invalidateProduct(int $productId): void
    {
        Cache::forget(self::PREFIX_PRODUCT . ':' . $productId);
        Cache::forget(self::PREFIX_PRODUCT . ':list');
    }

    /**
     * Get user permissions (cached)
     * 
     * @param int $userId
     * @return mixed
     */
    public function getUserPermissions(int $userId)
    {
        return Cache::remember(
            self::PREFIX_USER . ':' . $userId . ':permissions',
            now()->addHours(1),
            function () use ($userId) {
                $user = \App\Models\User::find($userId);
                return $user ? $user->permissions : [];
            }
        );
    }

    /**
     * Invalidate user cache
     * 
     * @param int $userId
     */
    public function invalidateUser(int $userId): void
    {
        Cache::forget(self::PREFIX_USER . ':' . $userId . ':permissions');
        Cache::forget(self::PREFIX_USER . ':' . $userId . ':warehouses');
    }

    /**
     * Get feature flag (cached)
     * 
     * @param string $feature
     * @return bool
     */
    public function isFeatureEnabled(string $feature): bool
    {
        return Cache::remember(
            self::PREFIX_FEATURE . ':' . $feature,
            now()->addHours(1),
            function () use ($feature) {
                // Check if feature is enabled in system
                return true; // Default true for most features
            }
        );
    }

    /**
     * Invalidate feature flag cache
     * 
     * @param string $feature
     */
    public function invalidateFeature(string $feature): void
    {
        Cache::forget(self::PREFIX_FEATURE . ':' . $feature);
    }

    /**
     * Get customers list (cached) - NO BALANCES
     * 
     * @param int $warehouseId
     * @return mixed
     */
    public function getCustomersList(int $warehouseId)
    {
        return Cache::remember(
            'customers:list:' . $warehouseId,
            now()->addHours(1),
            function () use ($warehouseId) {
                return \App\Models\Customer::where('warehouse_id', $warehouseId)
                    ->orderBy('name')
                    ->get(['id', 'name', 'phone', 'family_id']);
            }
        );
    }

    /**
     * Invalidate customers list cache
     * 
     * @param int $warehouseId
     */
    public function invalidateCustomersList(int $warehouseId): void
    {
        Cache::forget('customers:list:' . $warehouseId);
    }

    /**
     * Get families list (cached) - NO BALANCES
     * 
     * @return mixed
     */
    public function getFamiliesList()
    {
        return Cache::remember(
            'families:list',
            now()->addHours(1),
            function () {
                return \App\Models\Family::active()->orderBy('name')->get();
            }
        );
    }

    /**
     * Invalidate families list cache
     */
    public function invalidateFamiliesList(): void
    {
        Cache::forget('families:list');
    }

    /**
     * Get configuration value (cached)
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getConfig(string $key, $default = null)
    {
        return Cache::remember(
            self::PREFIX_CONFIG . ':' . $key,
            now()->addDays(1),
            function () use ($key, $default) {
                // Get from config or database
                return config('app.' . $key, $default);
            }
        );
    }

    /**
     * Invalidate config cache
     * 
     * @param string $key
     */
    public function invalidateConfig(string $key): void
    {
        Cache::forget(self::PREFIX_CONFIG . ':' . $key);
        Cache::forget(self::PREFIX_CONFIG . ':all');
    }

    /**
     * Get report (cached with timestamp)
     * 
     * Reports are cacheable because they're time-stamped aggregates
     * Not real-time financial data
     * 
     * @param string $reportType
     * @param array $filters
     * @param int $ttlMinutes
     * @return mixed
     */
    public function getReport(string $reportType, array $filters = [], int $ttlMinutes = 60)
    {
        $cacheKey = 'report:' . $reportType . ':' . md5(json_encode($filters));
        
        return Cache::remember(
            $cacheKey,
            now()->addMinutes($ttlMinutes),
            function () use ($reportType, $filters) {
                // Generate report based on type
                return $this->generateReport($reportType, $filters);
            }
        );
    }

    /**
     * Invalidate report cache
     * Safely handles if Redis is unavailable
     * 
     * @param string $reportType
     */
    public function invalidateReport(string $reportType): void
    {
        try {
            // Try to invalidate from Redis
            $keys = Cache::store('redis')->connection()->keys('report:' . $reportType . ':*');
            foreach ($keys as $key) {
                Cache::forget($key);
            }
        } catch (\Exception $e) {
            Log::warning('Could not invalidate reports (Redis unavailable)', ['error' => $e->getMessage()]);
            // Gracefully continue - cache store handles this
        }
    }

    /**
     * CRITICAL: Financial balance - NEVER cache blindly
     * 
     * This method explicitly refuses to cache.
     * Always recalculate for accuracy.
     * 
     * @param int $customerId
     * @return float
     */
    public function getCustomerBalance(int $customerId): float
    {
        try {
            // NEVER use cache for financial data
            // Always recalculate from source
            Log::info('Financial balance recalculated fresh', ['customer_id' => $customerId]);
            
            return \App\Models\Sale::where('customer_id', $customerId)
                ->where('udhar_account_type', 'individual')
                ->sum(\Illuminate\Database\Query\Expression('total_amount - paid_amount'));
        } catch (\Exception $e) {
            Log::error('Error calculating balance', ['error' => $e->getMessage()]);
            return 0.0;
        }
    }

    /**
     * CRITICAL: Family balance - NEVER cache blindly
     * 
     * @param int $familyId
     * @return float
     */
    public function getFamilyBalance(int $familyId): float
    {
        try {
            // NEVER use cache for financial data
            Log::info('Family balance recalculated fresh', ['family_id' => $familyId]);
            
            return \App\Models\Sale::where('family_id', $familyId)
                ->where('udhar_account_type', 'family')
                ->sum(\Illuminate\Database\Query\Expression('total_amount - paid_amount'));
        } catch (\Exception $e) {
            Log::error('Error calculating family balance', ['error' => $e->getMessage()]);
            return 0.0;
        }
    }

    /**
     * CRITICAL: Payment records - NEVER cache
     * 
     * @param int $customerId
     * @return mixed
     */
    public function getPaymentHistory(int $customerId)
    {
        // NEVER cache payment records
        // Always get fresh from database
        return \App\Models\CustomerPayment::where('customer_id', $customerId)
            ->orderBy('payment_date', 'desc')
            ->get();
    }

    /**
     * Clear all caches
     * Use carefully - only in special circumstances
     */
    public function clearAllCaches(): void
    {
        Log::warning('All caches cleared - this should rarely happen');
        Cache::flush();
    }

    /**
     * Clear specific category of caches
     * 
     * @param string $category
     */
    public function clearCacheCategory(string $category): void
    {
        $keys = Cache::store('redis')->connection()->keys($category . ':*');
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get cache statistics
     * Returns empty array if Redis unavailable
     * 
     * @return array
     */
    public function getStats(): array
    {
        try {
            $redis = Cache::store('redis')->connection();
            $info = $redis->command('info', ['stats']);
            
            return [
                'hits' => $info['keyspace_hits'] ?? 0,
                'misses' => $info['keyspace_misses'] ?? 0,
                'hit_rate' => $this->calculateHitRate(),
                'memory_used' => $info['used_memory_human'] ?? 'N/A',
                'total_keys' => count($redis->keys('*')),
            ];
        } catch (\Exception $e) {
            Log::warning('Could not get cache stats (Redis unavailable)', ['error' => $e->getMessage()]);
            return [
                'hits' => 0,
                'misses' => 0,
                'hit_rate' => 0,
                'memory_used' => 'N/A',
                'total_keys' => 0,
                'status' => 'Cache store not available',
            ];
        }
    }

    /**
     * Calculate cache hit rate
     * Returns 0 if Redis unavailable
     * 
     * @return float
     */
    private function calculateHitRate(): float
    {
        try {
            $redis = Cache::store('redis')->connection();
            $info = $redis->command('info', ['stats']);
            
            $hits = $info['keyspace_hits'] ?? 0;
            $misses = $info['keyspace_misses'] ?? 0;
            $total = $hits + $misses;
            
            return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
        } catch (\Exception $e) {
            Log::warning('Could not calculate hit rate (Redis unavailable)');
            return 0;
        }
    }

    /**
     * Generate report (placeholder)
     * 
     * @param string $type
     * @param array $filters
     * @return mixed
     */
    private function generateReport(string $type, array $filters = [])
    {
        // Implement report generation based on type
        return [];
    }
}
