<?php
/**
 * Setup Script for Expense Management Feature
 * Run this from the project root: php setup-expenses.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

echo "=== Expense Management Setup ===\n";
echo "Starting setup...\n\n";

// Step 1: Run migrations
echo "Step 1: Running migrations...\n";
$exitCode = Artisan::call('migrate', ['--force' => true]);
echo "Migrations completed. Exit code: $exitCode\n\n";

// Step 2: Seed permissions
echo "Step 2: Seeding permissions...\n";
$exitCode = Artisan::call('db:seed', ['--class' => 'PermissionSeeder', '--force' => true]);
echo "Permissions seeded. Exit code: $exitCode\n\n";

// Step 3: Seed roles
echo "Step 3: Seeding roles with expense permissions...\n";
$exitCode = Artisan::call('db:seed', ['--class' => 'RoleSeeder', '--force' => true]);
echo "Roles seeded. Exit code: $exitCode\n\n";

echo "=== Setup Complete ===\n";
echo "Expense Management feature is now ready!\n";
echo "Log in to see the 'Expense Management' option in the sidebar.\n";
