<?php
// Comprehensive debugging script for payment issue
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Sale;
use App\Models\Customer;
use App\Models\CustomerPayment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

echo "Starting debug script...\n\n";

try {
    // Simulate authentication
    $user = \App\Models\User::first();
    Auth::login($user);
    
    echo "✓ Authenticated as: " . Auth::user()->name . "\n\n";
    
    // Get the customer from screenshot (sohail with id 16)
    $customer = Customer::find(16);
    if (!$customer) {
        throw new Exception('Customer 16 not found');
    }
    echo "✓ Found customer: " . $customer->name . "\n";
    echo "  ID: " . $customer->id . "\n";
    echo "  Warehouse: " . $customer->warehouse_id . "\n\n";
    
    // Get first confirmed sale
    $sale = $customer->sales()->confirmed()->first();
    if (!$sale) {
        throw new Exception('No confirmed sales found for this customer');
    }
    echo "✓ Found sale: " . $sale->invoice_number . "\n";
    echo "  ID: " . $sale->id . "\n";
    echo "  Total: " . $sale->total_amount . "\n";
    echo "  Paid: " . $sale->paid_amount . "\n";
    echo "  Warehouse: " . $sale->warehouse_id . "\n\n";
    
    // Test 1: Query customer payments
    echo "TEST 1: Query customer payments\n";
    $start = microtime(true);
    $payments = $sale->customerPayments()->get();
    $duration = microtime(true) - $start;
    echo "  Count: " . $payments->count() . "\n";
    echo "  Duration: " . number_format($duration * 1000, 2) . "ms\n\n";
    
    // Test 2: Sum customer payments
    echo "TEST 2: Sum customer payments\n";
    $start = microtime(true);
    $sum = $sale->customerPayments()->sum('amount');
    $duration = microtime(true) - $start;
    echo "  Sum: " . $sum . "\n";
    echo "  Duration: " . number_format($duration * 1000, 2) . "ms\n\n";
    
    // Test 3: Calculate remaining
    echo "TEST 3: Calculate remaining udhar\n";
    $start = microtime(true);
    $totalPaid = $sale->paid_amount + $sum;
    $remaining = max(0, $sale->total_amount - $totalPaid);
    $duration = microtime(true) - $start;
    echo "  Remaining: " . $remaining . "\n";
    echo "  Duration: " . number_format($duration * 1000, 2) . "ms\n\n";
    
    // Test 4: Test accessor
    echo "TEST 4: Test current_remaining_udhar accessor\n";
    $start = microtime(true);
    $accessor = $sale->current_remaining_udhar;
    $duration = microtime(true) - $start;
    echo "  Value: " . $accessor . "\n";
    echo "  Duration: " . number_format($duration * 1000, 2) . "ms\n\n";
    
    // Test 5: Create payment in transaction
    echo "TEST 5: Create payment in transaction\n";
    $testAmount = 100.00;
    $start = microtime(true);
    
    DB::beginTransaction();
    try {
        $payment = CustomerPayment::create([
            'customer_id' => $sale->customer_id,
            'sale_id' => $sale->id,
            'amount' => $testAmount,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'test',
            'reference_number' => 'TEST_' . time(),
            'notes' => 'Test payment - will be rolled back',
            'received_by' => Auth::id(),
        ]);
        
        DB::rollBack();
        $duration = microtime(true) - $start;
        echo "  ✓ Payment created (rolled back)\n";
        echo "  Payment ID: " . $payment->id . "\n";
        echo "  Duration: " . number_format($duration * 1000, 2) . "ms\n\n";
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
    
    // Test 6: Refresh sale
    echo "TEST 6: Refresh sale\n";
    $start = microtime(true);
    $sale = $sale->refresh();
    $duration = microtime(true) - $start;
    echo "  ✓ Sale refreshed\n";
    echo "  Duration: " . number_format($duration * 1000, 2) . "ms\n\n";
    
    // Test 7: Get current payment status after refresh
    echo "TEST 7: Get current_payment_status accessor after refresh\n";
    $start = microtime(true);
    $status = $sale->current_payment_status;
    $duration = microtime(true) - $start;
    echo "  Status: " . $status . "\n";
    echo "  Duration: " . number_format($duration * 1000, 2) . "ms\n\n";
    
    echo "=== ALL TESTS PASSED ===\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

// Show logs
echo "\n\nRecent logs:\n";
$logContent = file_get_contents('storage/logs/laravel.log');
$lines = array_slice(explode("\n", $logContent), -20);
foreach ($lines as $line) {
    if (trim($line)) {
        echo $line . "\n";
    }
}
?>
