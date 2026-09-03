<?php
// Load Laravel
require __DIR__ . '/bootstrap/app.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Run migrations
echo "Running pending migrations...\n";
try {
    $kernel->call('migrate', ['--force' => true]);
    echo "Migrations completed successfully!\n";
} catch (Exception $e) {
    echo "Error running migrations: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
?>
