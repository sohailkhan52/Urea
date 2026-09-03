<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Customer;

$customers = Customer::active()->orderBy('name')->get();
echo "Total active customers: " . $customers->count() . "\n\n";

$sohailCount = Customer::where('name', 'sohail')->count();
echo "Customers named 'sohail': " . $sohailCount . "\n\n";

echo "First 10 customers:\n";
foreach ($customers->take(10) as $c) {
    echo "  ID: " . str_pad($c->id, 3) . " | Name: " . str_pad($c->name, 20) . " | Phone: " . ($c->phone ?? 'None') . "\n";
}

echo "\nFull customer list (all):\n";
foreach ($customers as $c) {
    echo "  ID: " . str_pad($c->id, 3) . " | Name: " . str_pad($c->name, 20) . " | Phone: " . ($c->phone ?? 'None') . "\n";
}
?>
