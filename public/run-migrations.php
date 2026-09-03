<?php
/**
 * Manual Migration Runner
 * Access this file in your browser: http://localhost:8000/run-migrations.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

// Run migrations
$kernel->call('migrate', ['--force' => true]);

echo "Migrations completed!";
?>
