<?php
/**
 * Check actual database schema
 * Open this in browser: http://localhost:8000/check_database.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

header('Content-Type: text/plain');

echo "=================================================\n";
echo "DATABASE SCHEMA CHECK\n";
echo "=================================================\n\n";

echo "Database: " . config('database.connections.mysql.database') . "\n";
echo "Host: " . config('database.connections.mysql.host') . "\n\n";

try {
    echo "=================================================\n";
    echo "1. SALES_RETURNS TABLE STRUCTURE\n";
    echo "=================================================\n\n";
    
    $columns = DB::select('SHOW COLUMNS FROM sales_returns');
    
    echo "Column Name              | Type                  | Null | Key\n";
    echo "-------------------------+-----------------------+------+-----\n";
    
    $hasFamilyId = false;
    $hasTotalReturnAmount = false;
    
    foreach($columns as $col) {
        printf("%-24s | %-21s | %-4s | %-3s\n", 
            $col->Field, 
            $col->Type, 
            $col->Null, 
            $col->Key
        );
        
        if ($col->Field === 'family_id') {
            $hasFamilyId = true;
        }
        if ($col->Field === 'total_return_amount') {
            $hasTotalReturnAmount = true;
        }
    }
    
    echo "\n";
    echo "=================================================\n";
    echo "MISSING COLUMNS CHECK\n";
    echo "=================================================\n\n";
    
    if (!$hasFamilyId) {
        echo "✗ MISSING: family_id column\n";
        echo "  This is causing the error!\n\n";
    } else {
        echo "✓ family_id column exists\n\n";
    }
    
    if (!$hasTotalReturnAmount) {
        echo "✗ MISSING: total_return_amount column\n";
        echo "  This may cause errors!\n\n";
    } else {
        echo "✓ total_return_amount column exists\n\n";
    }
    
    echo "=================================================\n";
    echo "2. CUSTOMERS.FAMILY_ID STRUCTURE\n";
    echo "=================================================\n\n";
    
    $customerColumns = DB::select("SHOW COLUMNS FROM customers WHERE Field = 'family_id'");
    
    if (!empty($customerColumns)) {
        $col = $customerColumns[0];
        echo "customers.family_id:\n";
        echo "  Type: " . $col->Type . "\n";
        echo "  Nullable: " . $col->Null . "\n";
        echo "  Key: " . $col->Key . "\n\n";
    } else {
        echo "✗ customers table does not have family_id\n\n";
    }
    
    echo "=================================================\n";
    echo "3. FAMILIES TABLE CHECK\n";
    echo "=================================================\n\n";
    
    $familiesExists = DB::select("SHOW TABLES LIKE 'families'");
    
    if (!empty($familiesExists)) {
        echo "✓ families table exists\n\n";
        
        $familyColumns = DB::select('SHOW COLUMNS FROM families');
        echo "Primary Key: ";
        foreach($familyColumns as $col) {
            if ($col->Key === 'PRI') {
                echo $col->Field . " (" . $col->Type . ")\n";
            }
        }
    } else {
        echo "✗ families table does not exist\n";
    }
    
    echo "\n";
    echo "=================================================\n";
    echo "4. MIGRATION STATUS\n";
    echo "=================================================\n\n";
    
    $migrations = DB::table('migrations')
        ->where('migration', 'like', '%family%')
        ->orWhere('migration', 'like', '%sales_returns%')
        ->orderBy('batch')
        ->get();
    
    echo "Relevant Migrations:\n\n";
    foreach($migrations as $migration) {
        echo "  Batch " . $migration->batch . ": " . $migration->migration . "\n";
    }
    
    echo "\n";
    echo "=================================================\n";
    echo "CONCLUSION\n";
    echo "=================================================\n\n";
    
    if (!$hasFamilyId || !$hasTotalReturnAmount) {
        echo "✗ DATABASE SCHEMA IS INCOMPLETE\n\n";
        echo "Required Actions:\n";
        
        if (!$hasFamilyId) {
            echo "  1. Add family_id column to sales_returns\n";
        }
        if (!$hasTotalReturnAmount) {
            echo "  2. Add total_return_amount column to sales_returns\n";
        }
        
        echo "\n";
        echo "Run the SQL file: add_family_to_returns.sql\n";
        echo "  OR\n";
        echo "Run: php artisan migrate\n";
    } else {
        echo "✓ DATABASE SCHEMA IS COMPLETE\n";
        echo "  All required columns exist.\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}
