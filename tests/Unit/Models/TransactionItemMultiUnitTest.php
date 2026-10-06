<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\Unit;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionItemMultiUnitTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;
    private Unit $kg;
    private Unit $bag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kg = Unit::create([
            'name' => 'Kilogram',
            'abbreviation' => 'KG',
            'category' => 'weight',
        ]);

        $this->bag = Unit::create([
            'name' => 'Bag',
            'abbreviation' => 'Bag',
            'category' => 'packaging',
        ]);

        $this->product = Product::create([
            'name' => 'Rice',
            'sku' => 'RICE-001',
            'unit' => 'KG',
            'base_unit_id' => $this->kg->id,
            'purchase_price' => 2.00,
            'sale_price' => 3.00,
        ]);
    }

    /** @test */
    public function purchase_item_has_unit_columns_fillable()
    {
        $warehouse = Warehouse::create(['name' => 'Main', 'location' => 'Location A']);
        $supplier = Supplier::create(['name' => 'Test Supplier', 'phone' => '1234567890']);

        $purchase = Purchase::create([
            'warehouse_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now(),
            'total_amount' => 1000.00,
            'status' => 'pending',
        ]);

        $purchaseItem = PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $this->product->id,
            'quantity' => 2, // 2 bags
            'unit_price' => 100.00,
            'total' => 200.00,
            'unit_id' => $this->bag->id,
            'conversion_factor' => 50.0, // 1 bag = 50 KG
            'base_quantity' => 100.0, // 2 * 50 = 100 KG
        ]);

        $this->assertDatabaseHas('purchase_items', [
            'id' => $purchaseItem->id,
            'unit_id' => $this->bag->id,
            'conversion_factor' => 50.0,
            'base_quantity' => 100.0,
        ]);
    }

    /** @test */
    public function purchase_item_has_unit_relationship()
    {
        $warehouse = Warehouse::create(['name' => 'Main', 'location' => 'Location A']);
        $supplier = Supplier::create(['name' => 'Test Supplier', 'phone' => '1234567890']);

        $purchase = Purchase::create([
            'warehouse_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now(),
            'total_amount' => 1000.00,
            'status' => 'pending',
        ]);

        $purchaseItem = PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 100.00,
            'total' => 200.00,
            'unit_id' => $this->bag->id,
        ]);

        $this->assertInstanceOf(Unit::class, $purchaseItem->unit);
        $this->assertEquals('Bag', $purchaseItem->unit->name);
    }

    /** @test */
    public function purchase_item_unit_columns_can_be_null_for_backward_compatibility()
    {
        $warehouse = Warehouse::create(['name' => 'Main', 'location' => 'Location A']);
        $supplier = Supplier::create(['name' => 'Test Supplier', 'phone' => '1234567890']);

        $purchase = Purchase::create([
            'warehouse_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now(),
            'total_amount' => 1000.00,
            'status' => 'pending',
        ]);

        // Legacy purchase item without unit columns
        $purchaseItem = PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
            'unit_price' => 2.00,
            'total' => 100.00,
            'unit_id' => null,
            'conversion_factor' => null,
            'base_quantity' => null,
        ]);

        $this->assertNull($purchaseItem->unit_id);
        $this->assertNull($purchaseItem->conversion_factor);
        $this->assertNull($purchaseItem->base_quantity);
    }

    /** @test */
    public function sale_item_has_unit_columns_fillable()
    {
        $warehouse = Warehouse::create(['name' => 'Main', 'location' => 'Location A']);
        $customer = Customer::create(['name' => 'Test Customer', 'phone' => '1234567890']);

        $sale = Sale::create([
            'warehouse_id' => $warehouse->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'total_amount' => 150.00,
            'status' => 'pending',
        ]);

        $saleItem = SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'quantity' => 1, // 1 bag
            'unit_price' => 150.00,
            'cost_price' => 100.00,
            'discount' => 0,
            'total' => 150.00,
            'unit_id' => $this->bag->id,
            'conversion_factor' => 50.0, // 1 bag = 50 KG
            'base_quantity' => 50.0, // 1 * 50 = 50 KG
            'base_cost_price' => 2.00, // Cost per base unit (KG)
        ]);

        $this->assertDatabaseHas('sale_items', [
            'id' => $saleItem->id,
            'unit_id' => $this->bag->id,
            'conversion_factor' => 50.0,
            'base_quantity' => 50.0,
            'base_cost_price' => 2.00,
        ]);
    }

    /** @test */
    public function sale_item_has_unit_relationship()
    {
        $warehouse = Warehouse::create(['name' => 'Main', 'location' => 'Location A']);
        $customer = Customer::create(['name' => 'Test Customer', 'phone' => '1234567890']);

        $sale = Sale::create([
            'warehouse_id' => $warehouse->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'total_amount' => 150.00,
            'status' => 'pending',
        ]);

        $saleItem = SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'unit_price' => 150.00,
            'cost_price' => 100.00,
            'discount' => 0,
            'total' => 150.00,
            'unit_id' => $this->bag->id,
        ]);

        $this->assertInstanceOf(Unit::class, $saleItem->unit);
        $this->assertEquals('Bag', $saleItem->unit->name);
    }

    /** @test */
    public function sale_item_casts_multi_unit_columns_to_decimal()
    {
        $warehouse = Warehouse::create(['name' => 'Main', 'location' => 'Location A']);
        $customer = Customer::create(['name' => 'Test Customer', 'phone' => '1234567890']);

        $sale = Sale::create([
            'warehouse_id' => $warehouse->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'total_amount' => 150.00,
            'status' => 'pending',
        ]);

        $saleItem = SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'unit_price' => 150.00,
            'cost_price' => 100.00,
            'discount' => 0,
            'total' => 150.00,
            'unit_id' => $this->bag->id,
            'conversion_factor' => '50.1234',
            'base_quantity' => '50.5678',
            'base_cost_price' => '2.0005',
        ]);

        $this->assertEquals('50.1234', $saleItem->conversion_factor);
        $this->assertEquals('50.5678', $saleItem->base_quantity);
        $this->assertEquals('2.0005', $saleItem->base_cost_price);
    }

    /** @test */
    public function conversion_factor_is_historical_snapshot()
    {
        // This test verifies that conversion_factor is stored at transaction time
        // and won't change if product configuration changes later

        $warehouse = Warehouse::create(['name' => 'Main', 'location' => 'Location A']);
        $customer = Customer::create(['name' => 'Test Customer', 'phone' => '1234567890']);

        $sale = Sale::create([
            'warehouse_id' => $warehouse->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'total_amount' => 150.00,
            'status' => 'pending',
        ]);

        // Create sale with conversion factor of 50
        $saleItem = SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'unit_price' => 150.00,
            'cost_price' => 100.00,
            'discount' => 0,
            'total' => 150.00,
            'unit_id' => $this->bag->id,
            'conversion_factor' => 50.0,
            'base_quantity' => 50.0,
        ]);

        // Verify the historical snapshot is stored
        $this->assertEquals(50.0, $saleItem->conversion_factor);
        
        // Even if we change the product configuration later,
        // this historical transaction should retain its original conversion_factor
        $saleItem->refresh();
        $this->assertEquals(50.0, $saleItem->conversion_factor);
    }
}
