<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Console\Command;

class SetPackageNames extends Command
{
    protected $signature = 'products:set-package-names';
    protected $description = 'Set package names for products that have multiple units without package names';

    public function handle()
    {
        // Get products with multiple ProductUnits
        $products = Product::has('productUnits', '>=', 2)->get();

        foreach ($products as $product) {
            $productUnits = $product->productUnits()->orderBy('conversion_to_base')->get();

            if ($productUnits->count() <= 1) {
                continue;
            }

            $this->info("Processing product: {$product->name}");

            // Check if any already have package_name
            $hasPackageNames = $productUnits->whereNotNull('package_name')->count() > 0;

            if (!$hasPackageNames) {
                // Auto-generate package names based on conversion factor
                foreach ($productUnits as $index => $pu) {
                    $packageName = '';

                    if ($pu->conversion_to_base == 1) {
                        $packageName = 'piece';
                    } elseif ($pu->conversion_to_base <= 12) {
                        $packageName = 'carton';
                    } else {
                        $packageName = 'box';
                    }

                    if (!$pu->package_name) {
                        $pu->update(['package_name' => $packageName]);
                        $this->line("  ✓ Set package_name='{$packageName}' for unit {$pu->unit->name} (conversion: {$pu->conversion_to_base})");
                    }
                }
            } else {
                $this->info("  Already has package names, skipping");
            }
        }

        $this->info("\n✓ Package names setup completed!");
    }
}
