<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\WarehouseInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseInventoryDecimalTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_store_decimal_quantities()
    {
        $unit = Unit::create([
            'name' => 'Kilogram',
            'abbreviation' => 'KG',
            'category' => 'weight',
        ]);

        $product = Product::create([
            'name' => 'Rice',
            'sku' => 'RICE-001',
            'unit' => 'KG',
            'base_unit_id' => $unit->id,
            'purchase_price' => 2.00,
            'sale_price' => 3.00,
        ]);

        $warehouse = Warehouse::create([
            'name' => 'Main Warehouse',
            'location' => 'Location A',
        ]);

        // Test decimal quantity with 4 decimal places
        $inventory = WarehouseInventory::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 50.5678,
        ]);

        $this->assertEquals('50.5678', $inventory->quantity);
        
        $this->assertDatabaseHas('warehouse_inventory', [
            'id' => $inventory->id,
            'quantity' => 50.5678,
        ]);
    }

    /** @test */
    public function it_casts_quantity_to_decimal_with_4_places()
    {
        $unit = Unit::create([
            'name' => 'Kilogram',
            'abbreviation' => 'KG',
            'category' => 'weight',
        ]);

        $product = Product::create([
            'name' => 'Rice',
            'sku' => 'RICE-001',
            'unit' => 'KG',
            'base_unit_id' => $unit->id,
            'purchase_price' => 2.00,
            'sale_price' => 3.00,
        ]);

        $warehouse = Warehouse::create([
            'name' => 'Main Warehouse',
            'location' => 'Location A',
        ]);

        $inventory = WarehouseInventory::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => '123.4567',
        ]);

        // Verify it's cast to decimal with proper precision
        $this->assertEquals('123.4567', $inventory->quantity);
    }

    /** @test */
    public function it_handles_fractional_quantities()
    {
        $unit = Unit::create([
            'name' => 'Kilogram',
            'abbreviation' => 'KG',
            'category' => 'weight',
        ]);

        $product = Product::create([
            'name' => 'Rice',
            'sku' => 'RICE-001',
            'unit' => 'KG',
            'base_unit_id' => $unit->id,
            'purchase_price' => 2.00,
            'sale_price' => 3.00,
        ]);

        $warehouse = Warehouse::create([
            'name' => 'Main Warehouse',
            'location' => 'Location A',
        ]);

        // Test various fractional quantities
        $testQuantities = [
            0.0001,  // Very small
            0.5,     // Half
            1.25,    // Quarter
            50.5,    // Half kilogram
            999.9999, // Almost 1000
        ];

        foreach ($testQuantities as $quantity) {
            $inventory = WarehouseInventory::create([
                'warehouse_id' => $warehouse->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);

            $this->assertEquals(number_format($quantity, 4, '.', ''), $inventory->quantity);
            
            // Clean up for next iteration
            $inventory->delete();
        }
    }

    /** @test */
    public function it_can_update_quantity_with_decimals()
    {
        $unit = Unit::create([
            'name' => 'Kilogram',
            'abbreviation' => 'KG',
            'category' => 'weight',
        ]);

        $product = Product::create([
            'name' => 'Rice',
            'sku' => 'RICE-001',
            'unit' => 'KG',
            'base_unit_id' => $unit->id,
            'purchase_price' => 2.00,
            'sale_price' => 3.00,
        ]);

        $warehouse = Warehouse::create([
            'name' => 'Main Warehouse',
            'location' => 'Location A',
        ]);

        $inventory = WarehouseInventory::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 100.0,
        ]);

        // Update with decimal quantity
        $inventory->update(['quantity' => 75.5678]);

        $this->assertEquals('75.5678', $inventory->fresh()->quantity);
    }

    /** @test */
    public function it_supports_fine_conversion_calculations()
    {
        // This test demonstrates that DECIMAL(15,4) supports fine conversions
        // Example: 1 Bag = 50 KG, selling 0.5 bags = 25 KG

        $unit = Unit::create([
            'name' => 'Kilogram',
            'abbreviation' => 'KG',
            'category' => 'weight',
        ]);

        $product = Product::create([
            'name' => 'Rice',
            'sku' => 'RICE-001',
            'unit' => 'KG',
            'base_unit_id' => $unit->id,
            'purchase_price' => 2.00,
            'sale_price' => 3.00,
        ]);

        $warehouse = Warehouse::create([
            'name' => 'Main Warehouse',
            'location' => 'Location A',
        ]);

        $inventory = WarehouseInventory::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 100.0, // 100 KG
        ]);

        // Simulate selling 0.5 bags (0.5 * 50 = 25 KG)
        $soldQuantity = 0.5 * 50;
        $inventory->update(['quantity' => $inventory->quantity - $soldQuantity]);

        $this->assertEquals('75.0000', $inventory->fresh()->quantity);
    }

    /** @test */
    public function it_handles_very_large_quantities()
    {
        $unit = Unit::create([
            'name' => 'Kilogram',
            'abbreviation' => 'KG',
            'category' => 'weight',
        ]);

        $product = Product::create([
            'name' => 'Rice',
            'sku' => 'RICE-001',
            'unit' => 'KG',
            'base_unit_id' => $unit->id,
            'purchase_price' => 2.00,
            'sale_price' => 3.00,
        ]);

        $warehouse = Warehouse::create([
            'name' => 'Main Warehouse',
            'location' => 'Location A',
        ]);

        // DECIMAL(15,4) supports up to 99,999,999,999.9999
        $largeQuantity = 99999999.9999;

        $inventory = WarehouseInventory::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => $largeQuantity,
        ]);

        $this->assertEquals(number_format($largeQuantity, 4, '.', ''), $inventory->quantity);
    }
}
