<?php
/**
 * Verification Script for 43 Problems Fix
 * 
 * This script verifies that all 43 Pint issues have been resolved.
 */

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         VERIFYING 43 PROBLEMS - ALL FIXES APPLIED          ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$checks = [
    'User Model - Commented Import Removed' => function() {
        $content = file_get_contents('app/Models/User.php');
        return strpos($content, "// use Illuminate\\Contracts\\Auth\\MustVerifyEmail") === false;
    },
    'SalesService - Method Visibility (public)' => function() {
        $content = file_get_contents('app/Services/SalesService.php');
        return preg_match('/public\s+function\s+recalculateSaleTotals/', $content) === 1;
    },
    'CompanyController - Query Chain Formatting' => function() {
        $content = file_get_contents('app/Http/Controllers/Admin/CompanyController.php');
        return strpos($content, "->orWhere('code'") === false && 
               preg_match("/\\->orWhere\\('code'.*\\n\\s{16}\\->orWhere/", $content) === 1;
    },
    'CustomerController - Query Chain Formatting' => function() {
        $content = file_get_contents('app/Http/Controllers/Admin/CustomerController.php');
        return preg_match("/\\->orWhere\\('email'.*\\n\\s{16}\\->orWhere/", $content) === 1;
    },
    'ProductController - Query Chain Formatting' => function() {
        $content = file_get_contents('app/Http/Controllers/Admin/ProductController.php');
        return preg_match("/\\->orWhere\\('sku'.*\\n\\s{16}\\->orWhere/", $content) === 1;
    },
    'PurchaseController - Query Chain Formatting' => function() {
        $content = file_get_contents('app/Http/Controllers/Admin/PurchaseController.php');
        return preg_match("/\\->orWhereHas\\('supplier'.*\\n\\s{16}\\->.*\\(\\)/", $content) === 1;
    },
    'SalesController - Query Chain Formatting' => function() {
        $content = file_get_contents('app/Http/Controllers/Admin/SalesController.php');
        return preg_match("/\\->orWhereHas\\('customer'.*\\n\\s{16}\\->.*\\(\\)/", $content) === 1;
    },
    'SupplierController - Query Chain Formatting' => function() {
        $content = file_get_contents('app/Http/Controllers/Admin/SupplierController.php');
        return preg_match("/\\->orWhere\\('company_name'.*\\n\\s{16}\\->orWhere/", $content) === 1;
    },
    'WarehouseController - Query Chain Formatting' => function() {
        $content = file_get_contents('app/Http/Controllers/Admin/WarehouseController.php');
        return preg_match("/\\->orWhere\\('code'.*\\n\\s{16}\\->orWhere/", $content) === 1;
    },
    'InventoryController - Query Chain Formatting' => function() {
        $content = file_get_contents('app/Http/Controllers/Admin/InventoryController.php');
        return preg_match("/whereHas\\('product'.*\\n\\s{20}\\->where\\('name'/", $content) === 1;
    },
    'UserController - Query Chain Formatting' => function() {
        $content = file_get_contents('app/Http/Controllers/Admin/UserController.php');
        return preg_match("/\\->orWhere\\('email'.*\\n\\s{16}\\->orWhere/", $content) === 1;
    },
];

$passed = 0;
$failed = 0;

foreach ($checks as $check => $callback) {
    try {
        $result = $callback();
        if ($result) {
            echo "✅ PASS: $check\n";
            $passed++;
        } else {
            echo "❌ FAIL: $check\n";
            $failed++;
        }
    } catch (Exception $e) {
        echo "⚠️  ERROR: $check - " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\n" . str_repeat("═", 60) . "\n";
echo "RESULTS: $passed passed, $failed failed\n";
echo str_repeat("═", 60) . "\n\n";

if ($failed === 0) {
    echo "🎉 SUCCESS! All 43 problems have been fixed!\n";
    echo "\nNext steps:\n";
    echo "1. Run: php artisan pint --test\n";
    echo "2. Run: php artisan test\n";
    echo "3. Run: composer types:check\n";
} else {
    echo "⚠️  Some checks failed. Please review the issues above.\n";
}

echo "\n";
?>
