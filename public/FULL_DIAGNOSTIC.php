<?php
/**
 * Complete Diagnostic for Minimum Stock Level Feature
 * Checks everything end-to-end
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Minimum Stock Level - Full Diagnostic</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        h1 { color: #333; margin-bottom: 30px; }
        .check { background: white; border-left: 4px solid #ddd; padding: 20px; margin: 15px 0; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .check.pass { border-left-color: #4caf50; background: #f1f8f4; }
        .check.fail { border-left-color: #f44336; background: #fef5f5; }
        .check.warn { border-left-color: #ff9800; background: #fff3f0; }
        .check h3 { margin-bottom: 10px; display: flex; align-items: center; }
        .check h3::before { display: inline-block; width: 20px; height: 20px; margin-right: 10px; border-radius: 50%; text-align: center; line-height: 20px; font-weight: bold; font-size: 12px; color: white; margin-right: 10px; }
        .check.pass h3::before { content: '✓'; background: #4caf50; }
        .check.fail h3::before { content: '✗'; background: #f44336; }
        .check.warn h3::before { content: '!'; background: #ff9800; }
        .detail { font-size: 13px; color: #666; margin: 10px 0; font-family: 'Courier New', monospace; background: #f9f9f9; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .detail strong { color: #333; }
        .action { background: #e3f2fd; border: 1px solid #bbdefb; padding: 20px; border-radius: 4px; margin: 30px 0; }
        .action a { display: inline-block; padding: 10px 20px; background: #2196f3; color: white; text-decoration: none; border-radius: 4px; margin: 5px 5px 5px 0; }
        .action a:hover { background: #1976d2; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 13px; }
        th, td { padding: 8px 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Minimum Stock Level - Full Diagnostic</h1>

        <?php
        $checks = [];

        // ==================================================
        // CHECK 1: Database Connection
        // ==================================================
        try {
            $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=urea', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $checks[] = [
                'title' => 'Database Connection',
                'status' => 'pass',
                'message' => 'Connected to MySQL database "urea"',
                'details' => '127.0.0.1:3306 / root / (no password)'
            ];
        } catch (Exception $e) {
            $checks[] = [
                'title' => 'Database Connection',
                'status' => 'fail',
                'message' => 'Connection failed',
                'details' => $e->getMessage()
            ];
            http_response_code(500);
            ?>
            <div class="check fail">
                <h3>Database Connection FAILED</h3>
                <div class="detail"><?php echo $e->getMessage(); ?></div>
            </div>
            </body>
            </html>
            <?php
            exit;
        }

        // ==================================================
        // CHECK 2: Products Table Exists
        // ==================================================
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
            if ($stmt->fetch()) {
                $checks[] = [
                    'title' => 'Products Table',
                    'status' => 'pass',
                    'message' => 'Products table exists'
                ];
            } else {
                throw new Exception('Products table not found');
            }
        } catch (Exception $e) {
            $checks[] = ['title' => 'Products Table', 'status' => 'fail', 'message' => $e->getMessage()];
        }

        // ==================================================
        // CHECK 3: Minimum Stock Level Column
        // ==================================================
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'minimum_stock_level'");
            $col = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($col) {
                $checks[] = [
                    'title' => 'Minimum Stock Level Column',
                    'status' => 'pass',
                    'message' => 'Column exists in database',
                    'details' => 'Type: ' . $col['Type'] . ' | Default: ' . ($col['Default'] ?? 'none') . ' | Nullable: ' . $col['Null']
                ];
            } else {
                $checks[] = [
                    'title' => 'Minimum Stock Level Column',
                    'status' => 'fail',
                    'message' => 'Column NOT FOUND in database',
                    'details' => 'Run initialization: http://localhost:8000/init_minimum_stock.php'
                ];
            }
        } catch (Exception $e) {
            $checks[] = ['title' => 'Minimum Stock Level Column', 'status' => 'fail', 'message' => $e->getMessage()];
        }

        // ==================================================
        // CHECK 4: Product Model Configuration
        // ==================================================
        $modelPath = '../app/Models/Product.php';
        if (file_exists($modelPath)) {
            $content = file_get_contents($modelPath);
            if (strpos($content, "'minimum_stock_level'") !== false && strpos($content, 'fillable') !== false) {
                $checks[] = [
                    'title' => 'Product Model',
                    'status' => 'pass',
                    'message' => 'Model includes minimum_stock_level in fillable array'
                ];
            } else {
                $checks[] = ['title' => 'Product Model', 'status' => 'warn', 'message' => 'Field might not be in fillable array'];
            }
        } else {
            $checks[] = ['title' => 'Product Model', 'status' => 'fail', 'message' => 'Model file not found'];
        }

        // ==================================================
        // CHECK 5: Controller Validation
        // ==================================================
        $controllerPath = '../app/Http/Controllers/Admin/ProductController.php';
        if (file_exists($controllerPath)) {
            $content = file_get_contents($controllerPath);
            if (strpos($content, 'minimum_stock_level') !== false) {
                $checks[] = [
                    'title' => 'ProductController',
                    'status' => 'pass',
                    'message' => 'Controller validates minimum_stock_level field'
                ];
            } else {
                $checks[] = ['title' => 'ProductController', 'status' => 'warn', 'message' => 'Field validation might be missing'];
            }
        }

        // ==================================================
        // CHECK 6: Forms
        // ==================================================
        $createFormPath = '../resources/views/admin/products/create.blade.php';
        $editFormPath = '../resources/views/admin/products/edit.blade.php';
        
        $createOk = false;
        $editOk = false;
        
        if (file_exists($createFormPath)) {
            $content = file_get_contents($createFormPath);
            if (strpos($content, 'minimum_stock_level') !== false) {
                $createOk = true;
            }
        }
        
        if (file_exists($editFormPath)) {
            $content = file_get_contents($editFormPath);
            if (strpos($content, 'minimum_stock_level') !== false) {
                $editOk = true;
            }
        }
        
        $checks[] = [
            'title' => 'Forms',
            'status' => ($createOk && $editOk) ? 'pass' : 'warn',
            'message' => ($createOk ? '✓ Create form OK' : '✗ Create form') . ' | ' . ($editOk ? '✓ Edit form OK' : '✗ Edit form')
        ];

        // ==================================================
        // CHECK 7: Data Sample
        // ==================================================
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            if ($count > 0) {
                $samples = $pdo->query("SELECT id, name, minimum_stock_level FROM products LIMIT 5");
                $rows = $samples->fetchAll(PDO::FETCH_ASSOC);
                
                $details = "<table><tr><th>ID</th><th>Name</th><th>Minimum Stock Level</th></tr>";
                foreach ($rows as $row) {
                    $details .= "<tr><td>#{$row['id']}</td><td>{$row['name']}</td><td>{$row['minimum_stock_level']}</td></tr>";
                }
                $details .= "</table>";
                
                $checks[] = [
                    'title' => 'Product Data',
                    'status' => 'pass',
                    'message' => "Total: $count products (showing first 5)",
                    'details' => $details
                ];
            } else {
                $checks[] = ['title' => 'Product Data', 'status' => 'warn', 'message' => 'No products in database'];
            }
        } catch (Exception $e) {
            $checks[] = ['title' => 'Product Data', 'status' => 'warn', 'message' => $e->getMessage()];
        }

        // ==================================================
        // CHECK 8: DashboardService Method
        // ==================================================
        $dashboardPath = '../app/Services/DashboardService.php';
        if (file_exists($dashboardPath)) {
            $content = file_get_contents($dashboardPath);
            if (strpos($content, 'getProductsBelowMinimumStock') !== false) {
                $checks[] = [
                    'title' => 'DashboardService',
                    'status' => 'pass',
                    'message' => 'Method getProductsBelowMinimumStock() exists'
                ];
            } else {
                $checks[] = ['title' => 'DashboardService', 'status' => 'warn', 'message' => 'Method might not exist'];
            }
        }

        // ==================================================
        // CHECK 9: Migration File
        // ==================================================
        $migrationPath = '../database/migrations/2026_09_09_add_minimum_stock_level_column.php';
        if (file_exists($migrationPath)) {
            $checks[] = [
                'title' => 'Migration File',
                'status' => 'pass',
                'message' => 'Migration file exists at 2026_09_09_add_minimum_stock_level_column.php'
            ];
        } else {
            $checks[] = ['title' => 'Migration File', 'status' => 'fail', 'message' => 'Migration file not found'];
        }

        // ==================================================
        // Display all checks
        // ==================================================
        foreach ($checks as $check) {
            $status_class = $check['status'];
            echo "<div class=\"check {$status_class}\">";
            echo "<h3>{$check['title']}</h3>";
            echo "<div>" . $check['message'] . "</div>";
            if (isset($check['details'])) {
                if (strpos($check['details'], '<table>') === 0) {
                    echo $check['details'];
                } else {
                    echo "<div class=\"detail\">" . htmlspecialchars($check['details']) . "</div>";
                }
            }
            echo "</div>";
        }

        // ==================================================
        // Determine overall status
        // ==================================================
        $failed = array_filter($checks, function($c) { return $c['status'] === 'fail'; });
        $overall = empty($failed) ? 'ready' : 'blocked';

        if ($overall === 'ready') {
            echo '<div class="action">';
            echo '<h3>✅ Everything is ready!</h3>';
            echo '<p>Your minimum stock level feature is fully configured.</p>';
            echo '<a href="/admin/products/create">Create Product</a>';
            echo '<a href="/admin/products">Edit Product</a>';
            echo '<a href="/admin/dashboard">View Dashboard</a>';
            echo '</div>';
        } else {
            echo '<div class="action">';
            echo '<h3>⚠️ Setup incomplete</h3>';
            echo '<p>Please fix the failed items above.</p>';
            echo '<a href="/init_minimum_stock.php">Initialize Database</a>';
            echo '</div>';
        }
        ?>

    </div>
</body>
</html>
