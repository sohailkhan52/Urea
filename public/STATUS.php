<?php
/**
 * Minimum Stock Level Feature - Complete Status Check
 */

header('Content-Type: text/html; charset=utf-8');

$status = [
    'database_column' => 'CHECKING',
    'product_model' => 'CHECKING',
    'controller_validation' => 'CHECKING',
    'forms' => 'CHECKING',
    'migration_file' => 'CHECKING',
];

// Try database connection
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=urea', 'root', '');
    
    // Check column
    $check = $pdo->query("SHOW COLUMNS FROM products LIKE 'minimum_stock_level'");
    if ($check->fetch()) {
        $status['database_column'] = '✅ EXISTS';
    } else {
        $status['database_column'] = '⏳ MISSING (needs initialization)';
    }
} catch (Exception $e) {
    $status['database_column'] = '❌ ERROR: ' . $e->getMessage();
}

// Check Product model
if (file_exists('../app/Models/Product.php')) {
    $content = file_get_contents('../app/Models/Product.php');
    if (strpos($content, 'minimum_stock_level') !== false && strpos($content, 'fillable') !== false) {
        $status['product_model'] = '✅ CONFIGURED';
    } else {
        $status['product_model'] = '⚠️ INCOMPLETE';
    }
} else {
    $status['product_model'] = '❌ NOT FOUND';
}

// Check controller
if (file_exists('../app/Http/Controllers/Admin/ProductController.php')) {
    $content = file_get_contents('../app/Http/Controllers/Admin/ProductController.php');
    if (strpos($content, "minimum_stock_level") !== false) {
        $status['controller_validation'] = '✅ CONFIGURED';
    } else {
        $status['controller_validation'] = '⚠️ INCOMPLETE';
    }
} else {
    $status['controller_validation'] = '❌ NOT FOUND';
}

// Check forms
$createForm = file_exists('../resources/views/admin/products/create.blade.php') ? true : false;
$editForm = file_exists('../resources/views/admin/products/edit.blade.php') ? true : false;

if ($createForm || $editForm) {
    $status['forms'] = '✅ PRESENT';
} else {
    $status['forms'] = '❌ NOT FOUND';
}

// Check migration
if (file_exists('../database/migrations/2026_09_09_add_minimum_stock_level_column.php')) {
    $status['migration_file'] = '✅ EXISTS';
} else {
    $status['migration_file'] = '❌ NOT FOUND';
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Minimum Stock Level - Status Check</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .status-item { padding: 15px; margin: 10px 0; background: #f9f9f9; border-left: 4px solid #ddd; }
        .status-item.ok { border-left-color: #4caf50; background: #f1f8f4; }
        .status-item.warning { border-left-color: #ff9800; background: #fff3f0; }
        .status-item.error { border-left-color: #f44336; background: #fef5f5; }
        .label { font-weight: bold; color: #555; }
        .action { margin-top: 30px; padding: 20px; background: #e3f2fd; border-radius: 8px; }
        .action a { display: inline-block; padding: 10px 20px; margin: 5px 5px 5px 0; background: #2196f3; color: white; text-decoration: none; border-radius: 4px; }
        .action a:hover { background: #1976d2; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Minimum Stock Level Feature - Status Check</h1>
        
        <div class="status-item <?php echo strpos($status['database_column'], '✅') === 0 ? 'ok' : (strpos($status['database_column'], '⏳') === 0 ? 'warning' : 'error'); ?>">
            <div class="label">Database Column:</div>
            <div><?php echo $status['database_column']; ?></div>
        </div>
        
        <div class="status-item <?php echo strpos($status['product_model'], '✅') === 0 ? 'ok' : 'error'; ?>">
            <div class="label">Product Model:</div>
            <div><?php echo $status['product_model']; ?></div>
        </div>
        
        <div class="status-item <?php echo strpos($status['controller_validation'], '✅') === 0 ? 'ok' : 'error'; ?>">
            <div class="label">Controller Validation:</div>
            <div><?php echo $status['controller_validation']; ?></div>
        </div>
        
        <div class="status-item <?php echo strpos($status['forms'], '✅') === 0 ? 'ok' : 'error'; ?>">
            <div class="label">Forms:</div>
            <div><?php echo $status['forms']; ?></div>
        </div>
        
        <div class="status-item <?php echo strpos($status['migration_file'], '✅') === 0 ? 'ok' : 'error'; ?>">
            <div class="label">Migration File:</div>
            <div><?php echo $status['migration_file']; ?></div>
        </div>
        
        <div class="action">
            <h3>Next Steps:</h3>
            
            <?php if (strpos($status['database_column'], 'MISSING') !== false): ?>
                <p><strong>⚡ Database column needs to be created!</strong></p>
                <a href="/init_minimum_stock.php" target="_blank">🚀 Initialize Database</a>
                (Opens in new tab, takes 2-3 seconds)
            <?php elseif (strpos($status['database_column'], '✅') === 0): ?>
                <p><strong>✅ Database column exists! Feature is ready to use!</strong></p>
                <a href="/admin/products/create">📝 Create Product</a>
                <a href="/admin/products">✏️ Edit Product</a>
                <a href="/admin/dashboard">📊 View Dashboard</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
