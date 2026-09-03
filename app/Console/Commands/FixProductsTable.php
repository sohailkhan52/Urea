<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixProductsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:fix-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix products table schema by adding unit column if missing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== PRODUCT TABLE FIX ===');
        $this->newLine();

        try {
            // Test database connection
            $this->info('1. Testing database connection...');
            DB::connection()->getPdo();
            $this->info('   ✓ Connected successfully');
            $this->newLine();

            // Check if products table exists
            $this->info('2. Checking products table...');
            if (!Schema::hasTable('products')) {
                $this->error('   ✗ Products table does not exist!');
                $this->info('   Run: php artisan migrate');
                return 1;
            }
            $this->info('   ✓ Products table exists');
            $this->newLine();

            // Check if unit column exists
            $this->info('3. Checking for unit column...');
            if (!Schema::hasColumn('products', 'unit')) {
                $this->warn('   ⚠ Unit column missing - adding now...');
                
                Schema::table('products', function ($table) {
                    $table->string('unit')->default('KG')->comment('KG, MG, or Piece')->after('name');
                });
                
                $this->info('   ✓ Unit column added successfully');
            } else {
                $this->info('   ✓ Unit column already exists');
            }
            $this->newLine();

            // Verify table structure
            $this->info('4. Current table structure:');
            $columns = Schema::getColumns('products');
            foreach ($columns as $col) {
                $this->line("   - {$col['name']}: {$col['type']}");
            }
            $this->newLine();

            // Test product creation
            $this->info('5. Testing product creation...');
            $test_name = 'Fix Test Product ' . time();
            
            try {
                $product_id = DB::table('products')->insertGetId([
                    'name' => $test_name,
                    'unit' => 'KG',
                    'purchase_price' => 100.00,
                    'sale_price' => 150.00,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $this->info("   ✓ Test product created with ID: {$product_id}");
                
                // Clean up
                DB::table('products')->where('id', $product_id)->delete();
                $this->info('   ✓ Test product cleaned up');
                
            } catch (\Exception $e) {
                $this->error("   ✗ Test failed: " . $e->getMessage());
                throw $e;
            }
            
            $this->newLine();
            $this->info('=== ALL CHECKS PASSED ===');
            $this->info('Your products table is ready!');
            $this->newLine();
            $this->info('You can now create products via the purchase form.');
            
            return 0;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('ERROR: ' . $e->getMessage());
            $this->newLine();
            $this->info('If the issue persists:');
            $this->info('1. Check your database connection in .env');
            $this->info('2. Run: php artisan migrate');
            $this->info('3. Run: php artisan config:clear');
            
            return 1;
        }
    }
}
