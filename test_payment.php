<?php
// Quick test to verify database connectivity
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Sale;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

Log::debug('=== TEST PAYMENT SCRIPT START ===');

try {
    Log::debug('Fetching customer 16');
    $customer = Customer::find(16);
    Log::debug('Customer found: ' . ($customer ? $customer->name : 'NOT FOUND'));
    
    if ($customer) {
        Log::debug('Fetching confirmed sales for customer');
        $sales = $customer->sales()->confirmed()->get();
        Log::debug('Sales count: ' . $sales->count());
        
        if ($sales->count() > 0) {
            $sale = $sales->first();
            Log::debug('First sale ID: ' . $sale->id);
            Log::debug('Sale total_amount: ' . $sale->total_amount);
            Log::debug('Sale paid_amount: ' . $sale->paid_amount);
            
            Log::debug('Fetching customer payments');
            $payments = $sale->customerPayments()->get();
            Log::debug('Customer payments count: ' . $payments->count());
            
            Log::debug('Summing payments');
            $sum = $sale->customerPayments()->sum('amount');
            Log::debug('Payment sum: ' . $sum);
            
            Log::debug('Calculating remaining');
            $remaining = max(0, $sale->total_amount - ($sale->paid_amount + $sum));
            Log::debug('Remaining udhar: ' . $remaining);
        }
    }
    
    Log::debug('=== TEST PAYMENT SCRIPT SUCCESS ===');
} catch (\Exception $e) {
    Log::error('=== TEST PAYMENT SCRIPT ERROR ===');
    Log::error('Error: ' . $e->getMessage());
    Log::error('Trace: ' . $e->getTraceAsString());
}

echo "Test completed. Check storage/logs/laravel.log";
?>
