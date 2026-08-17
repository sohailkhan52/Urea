<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get companies
        $ffc = Company::where('code', 'FFC')->first();
        $engro = Company::where('code', 'ENGRO')->first();
        $fatima = Company::where('code', 'FATIMA')->first();

        // Get categories
        $urea = Category::where('slug', 'urea')->first();
        $dap = Category::where('slug', 'dap')->first();
        $npk = Category::where('slug', 'npk')->first();

        if (!$ffc || !$engro || !$fatima || !$urea || !$dap || !$npk) {
            $this->command->error('Please seed companies and categories first!');
            return;
        }

        $products = [
            // FFC Products
            [
                'company_id' => $ffc->id,
                'category_id' => $urea->id,
                'name' => 'Sona Urea',
                'sku' => 'SONA-UREA-50',
                'barcode' => '8961234567890',
                'bag_weight' => 50.00,
                'weight_unit' => 'KG',
                'purchase_price' => 3800.00,
                'sale_price' => 4200.00,
                'minimum_stock_level' => 100,
                'description' => 'Premium quality urea fertilizer from Fauji Fertilizer Company. Contains 46% nitrogen.',
                'status' => 'active',
            ],
            [
                'company_id' => $ffc->id,
                'category_id' => $dap->id,
                'name' => 'FFC DAP',
                'sku' => 'FFC-DAP-50',
                'barcode' => '8961234567891',
                'bag_weight' => 50.00,
                'weight_unit' => 'KG',
                'purchase_price' => 8500.00,
                'sale_price' => 9200.00,
                'minimum_stock_level' => 50,
                'description' => 'Diammonium Phosphate fertilizer with 18% nitrogen and 46% phosphorus.',
                'status' => 'active',
            ],

            // Engro Products
            [
                'company_id' => $engro->id,
                'category_id' => $urea->id,
                'name' => 'Engro Zarkhez Urea',
                'sku' => 'ENGRO-UREA-50',
                'barcode' => '8961234567892',
                'bag_weight' => 50.00,
                'weight_unit' => 'KG',
                'purchase_price' => 3850.00,
                'sale_price' => 4250.00,
                'minimum_stock_level' => 150,
                'description' => 'High-quality urea from Engro Fertilizers. Ideal for all crops.',
                'status' => 'active',
            ],
            [
                'company_id' => $engro->id,
                'category_id' => $dap->id,
                'name' => 'Engro DAP',
                'sku' => 'ENGRO-DAP-50',
                'barcode' => '8961234567893',
                'bag_weight' => 50.00,
                'weight_unit' => 'KG',
                'purchase_price' => 8600.00,
                'sale_price' => 9300.00,
                'minimum_stock_level' => 75,
                'description' => 'Premium DAP fertilizer for enhanced crop yield.',
                'status' => 'active',
            ],
            [
                'company_id' => $engro->id,
                'category_id' => $npk->id,
                'name' => 'Engro NPK 15-15-15',
                'sku' => 'ENGRO-NPK-15-50',
                'barcode' => '8961234567894',
                'bag_weight' => 50.00,
                'weight_unit' => 'KG',
                'purchase_price' => 6500.00,
                'sale_price' => 7200.00,
                'minimum_stock_level' => 50,
                'description' => 'Balanced NPK fertilizer with equal ratio of nutrients.',
                'status' => 'active',
            ],

            // Fatima Products
            [
                'company_id' => $fatima->id,
                'category_id' => $urea->id,
                'name' => 'Fatima Urea',
                'sku' => 'FATIMA-UREA-50',
                'barcode' => '8961234567895',
                'bag_weight' => 50.00,
                'weight_unit' => 'KG',
                'purchase_price' => 3750.00,
                'sale_price' => 4150.00,
                'minimum_stock_level' => 200,
                'description' => 'Quality urea fertilizer from Fatima Fertilizer Company.',
                'status' => 'active',
            ],
            [
                'company_id' => $fatima->id,
                'category_id' => $npk->id,
                'name' => 'Fatima NPK 20-20-20',
                'sku' => 'FATIMA-NPK-20-50',
                'barcode' => '8961234567896',
                'bag_weight' => 50.00,
                'weight_unit' => 'KG',
                'purchase_price' => 7000.00,
                'sale_price' => 7800.00,
                'minimum_stock_level' => 60,
                'description' => 'High concentration NPK fertilizer for all crops.',
                'status' => 'active',
            ],

            // Additional products with different weights
            [
                'company_id' => $ffc->id,
                'category_id' => $urea->id,
                'name' => 'Sona Urea (25 KG)',
                'sku' => 'SONA-UREA-25',
                'barcode' => '8961234567897',
                'bag_weight' => 25.00,
                'weight_unit' => 'KG',
                'purchase_price' => 1900.00,
                'sale_price' => 2100.00,
                'minimum_stock_level' => 150,
                'description' => 'Small pack Sona Urea for retail customers.',
                'status' => 'active',
            ],
            [
                'company_id' => $engro->id,
                'category_id' => $urea->id,
                'name' => 'Engro Zarkhez Urea (25 KG)',
                'sku' => 'ENGRO-UREA-25',
                'barcode' => '8961234567898',
                'bag_weight' => 25.00,
                'weight_unit' => 'KG',
                'purchase_price' => 1925.00,
                'sale_price' => 2125.00,
                'minimum_stock_level' => 100,
                'description' => 'Small pack Engro Urea for small farmers.',
                'status' => 'active',
            ],

            // Inactive product example
            [
                'company_id' => $ffc->id,
                'category_id' => $dap->id,
                'name' => 'FFC DAP (Old Formula)',
                'sku' => 'FFC-DAP-OLD-50',
                'barcode' => '8961234567899',
                'bag_weight' => 50.00,
                'weight_unit' => 'KG',
                'purchase_price' => 8000.00,
                'sale_price' => 8800.00,
                'minimum_stock_level' => 25,
                'description' => 'Discontinued product - old formula.',
                'status' => 'inactive',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        $this->command->info('Products seeded successfully!');
    }
}
