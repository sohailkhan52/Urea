<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\Unit;
use App\Models\ProductUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductMultiUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_base_unit_relationship()
    {
        $unit = Unit::create([
            'name' => 'Kilogram',
            'abbreviation' => 'KG',
            'category' => 'weight',
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'unit' => 'KG',
            'base_unit_id' => $unit->id,
            'purchase_price' => 100.00,
            'sale_price' => 150.00,
        ]);

        $this->assertInstanceOf(Unit::class, $product->baseUnit);
        $this->assertEquals('Kilogram', $product->baseUnit->name);
    }

    /** @test */
    public function it_has_product_units_relationship()
    {
        $unit = Unit::create([
            'name' => 'Kilogram',
            'abbreviation' => 'KG',
            'category' => 'weight',
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'unit' => 'KG',
            'base_unit_id' => $unit->id,
            'purchase_price' => 100.00,
            'sale_price' => 150.00,
        ]);

        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $unit->id,
            'conversion_to_base' => 1.0,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $product->productUnits);
        $this->assertCount(1, $product->productUnits);
    }

    /** @test */
    public function it_can_have_multiple_product_units()
    {
        $kg = Unit::create(['name' => 'Kilogram', 'abbreviation' => 'KG', 'category' => 'weight']);
        $bag = Unit::create(['name' => 'Bag', 'abbreviation' => 'Bag', 'category' => 'packaging']);
        $ton = Unit::create(['name' => 'Ton', 'abbreviation' => 'Ton', 'category' => 'weight']);

        $product = Product::create([
            'name' => 'Rice',
            'sku' => 'RICE-001',
            'unit' => 'KG',
            'base_unit_id' => $kg->id,
            'purchase_price' => 100.00,
            'sale_price' => 150.00,
        ]);

        // Base unit (KG)
        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $kg->id,
            'conversion_to_base' => 1.0,
        ]);

        // Bag = 50 KG
        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $bag->id,
            'conversion_to_base' => 50.0,
        ]);

        // Ton = 1000 KG
        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $ton->id,
            'conversion_to_base' => 1000.0,
        ]);

        $product->refresh();

        $this->assertCount(3, $product->productUnits);
    }

    /** @test */
    public function base_unit_id_is_fillable()
    {
        $unit = Unit::create([
            'name' => 'Kilogram',
            'abbreviation' => 'KG',
            'category' => 'weight',
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'unit' => 'KG',
            'base_unit_id' => $unit->id,
            'purchase_price' => 100.00,
            'sale_price' => 150.00,
        ]);

        $this->assertEquals($unit->id, $product->base_unit_id);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'base_unit_id' => $unit->id,
        ]);
    }

    /** @test */
    public function base_unit_id_can_be_null_for_backward_compatibility()
    {
        $product = Product::create([
            'name' => 'Legacy Product',
            'sku' => 'LEG-001',
            'unit' => 'KG',
            'base_unit_id' => null,
            'purchase_price' => 100.00,
            'sale_price' => 150.00,
        ]);

        $this->assertNull($product->base_unit_id);
        $this->assertNull($product->baseUnit);
    }

    /** @test */
    public function it_preserves_legacy_unit_enum_column()
    {
        $unit = Unit::create([
            'name' => 'Kilogram',
            'abbreviation' => 'KG',
            'category' => 'weight',
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'unit' => 'KG', // Legacy ENUM column
            'base_unit_id' => $unit->id, // New multi-unit column
            'purchase_price' => 100.00,
            'sale_price' => 150.00,
        ]);

        $this->assertEquals('KG', $product->unit);
        $this->assertEquals($unit->id, $product->base_unit_id);
    }
}
