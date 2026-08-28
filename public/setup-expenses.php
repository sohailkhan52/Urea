<?php
/**
 * Expense Management Setup Helper
 * Access via: http://localhost:8000/setup-expenses.php
 */

// Check if this is running from web or CLI
if (php_sapi_name() !== 'cli') {
    echo "<h1>Expense Management Setup</h1>";
    echo "<pre>";
}

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use App\Models\Permission;
use App\Models\Role;

try {
    echo "Step 1: Creating/syncing permissions...\n";
    $permissions = [
        ['name' => 'View Expenses', 'slug' => 'expenses.view', 'group' => 'expenses', 'description' => 'View list of expenses'],
        ['name' => 'Create Expenses', 'slug' => 'expenses.create', 'group' => 'expenses', 'description' => 'Create new expenses'],
        ['name' => 'Edit Expenses', 'slug' => 'expenses.edit', 'group' => 'expenses', 'description' => 'Edit existing expenses'],
        ['name' => 'Delete Expenses', 'slug' => 'expenses.delete', 'group' => 'expenses', 'description' => 'Delete expenses'],
    ];

    $createdCount = 0;
    foreach ($permissions as $perm) {
        $created = Permission::firstOrCreate(['slug' => $perm['slug']], $perm);
        if ($created->wasRecentlyCreated) {
            $createdCount++;
        }
    }
    echo "✓ Permissions: {$createdCount} created, " . (count($permissions) - $createdCount) . " already existed\n\n";

    echo "Step 2: Assigning permissions to Admin role...\n";
    $adminRole = Role::where('slug', 'admin')->first();
    if ($adminRole) {
        $permissionSlugs = ['expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete'];
        $permissions = Permission::whereIn('slug', $permissionSlugs)->get();
        $adminRole->permissions()->syncWithoutDetaching($permissions);
        echo "✓ Assigned " . $permissions->count() . " permissions to Admin role\n\n";
    } else {
        echo "✗ Admin role not found\n\n";
    }

    echo "Step 3: Running migrations...\n";
    $output = Artisan::call('migrate', ['--force' => true]);
    echo "✓ Migrations completed\n\n";

    echo "=== Setup Complete ===\n";
    echo "✓ Expense Management feature is ready!\n";
    echo "✓ Please refresh your browser and log in again.\n";
    echo "✓ You should now see 'Expense Management' in the sidebar.\n";

    if (php_sapi_name() !== 'cli') {
        echo "</pre>";
    }

} catch (\Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    if (php_sapi_name() !== 'cli') {
        echo "</pre>";
    }
    exit(1);
}
