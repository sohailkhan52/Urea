<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\Unit;
use App\Models\ProductUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductUnitTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = Unit::create([
            'name' => 'Kilogram',
            'abbreviation' => 'KG',
            'category' => 'weight',
        ]);

        $this->product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'unit' => 'KG',
            'base_unit_id' => $this->unit->id,
            'purchase_price' => 100.00,
            'sale_price' => 150.00,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function it_can_create_a_product_unit()
    {
        $productUnit = ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_id' => $this->unit->id,
            'conversion_to_base' => 1.0,
            'purchase_price' => 100.00,
            'sale_price' => 150.00,
        ]);

        $this->assertDatabaseHas('product_units', [
            'product_id' => $this->product->id,
            'unit_id' => $this->unit->id,
            'conversion_to_base' => 1.0,
        ]);
    }

    /** @test */
    public function it_enforces_unique_product_unit_combination()
    {
        ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_id' => $this->unit->id,
            'conversion_to_base' => 1.0,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_id' => $this->unit->id,
            'conversion_to_base' => 2.0,
        ]);
    }

    /** @test */
    public function it_validates_positive_conversion_factor_on_save()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Conversion factor must be greater than 0');

        ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_id' => $this->unit->id,
            'conversion_to_base' => 0,
        ]);
    }

    /** @test */
    public function it_can_convert_to_base()
    {
        $productUnit = ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_id' => $this->unit->id,
            'conversion_to_base' => 50.0, // 1 unit = 50 base units
        ]);

        $result = $productUnit->convertToBase(2); // 2 units

        $this->assertEquals(100.0, $result); // 2 * 50 = 100
    }

    /** @test */
    public function it_can_convert_from_base()
    {
        $productUnit = ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_id' => $this->unit->id,
            'conversion_to_base' => 50.0, // 1 unit = 50 base units
        ]);

        $result = $productUnit->convertFromBase(100); // 100 base units

        $this->assertEquals(2.0, $result); // 100 / 50 = 2
    }

    /** @test */
    public function it_identifies_base_unit()
    {
        $baseUnit = ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_id' => $this->unit->id,
            'conversion_to_base' => 1.0,
        ]);

        $this->assertTrue($baseUnit->isBaseUnit());

        $secondaryUnit = Unit::create([
            'name' => 'Bag',
            'abbreviation' => 'Bag',
            'category' => 'packaging',
        ]);

        $nonBaseUnit = ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_id' => $secondaryUnit->id,
            'conversion_to_base' => 50.0,
        ]);

        $this->assertFalse($nonBaseUnit->isBaseUnit());
    }

    /** @test */
    public function it_has_effective_purchase_price()
    {
        // Test with explicit price
        $productUnit = ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_id' => $this->unit->id,
            'conversion_to_base' => 1.0,
            'purchase_price' => 120.00,
        ]);

        $this->assertEquals(120.00, $productUnit->effective_purchase_price);

        // Test fallback to product price
        $productUnitNullPrice = ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_id' => Unit::create(['name' => 'Gram', 'abbreviation' => 'G', 'category' => 'weight'])->id,
            'conversion_to_base' => 0.001,
            'purchase_price' => null,
        ]);

        $this->assertEquals(100.00, $productUnitNullPrice->effective_purchase_price);
    }

    /** @test */
    public function it_has_effective_sale_price()
    {
        // Test with explicit price
        $productUnit = ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_id' => $this->unit->id,
            'conversion_to_base' => 1.0,
            'sale_price' => 180.00,
        ]);

        $this->assertEquals(180.00, $productUnit->effective_sale_price);

        // Test fallback to product price
        $productUnitNullPrice = ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_id' => Unit::create(['name' => 'Gram', 'abbreviation' => 'G', 'category' => 'weight'])->id,
            'conversion_to_base' => 0.001,
            'sale_price' => null,
        ]);

        $this->assertEquals(150.00, $productUnitNullPrice->effective_sale_price);
    }

    /** @test */
    public function it_has_display_label()
    {
        $productUnit = ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_id' => $this->unit->id,
            'conversion_to_base' => 1.0,
        ]);

        $this->assertEquals('Test Product - Kilogram (KG)', $productUnit->display_label);
    }

    /** @test */
    public function it_has_active_scope()
    {
        ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_id' => $this->unit->id,
            'conversion_to_base' => 1.0,
            'is_active' => true,
        ]);

        $inactiveUnit = Unit::create(['name' => 'Inactive', 'abbreviation' => 'IN', 'category' => 'count']);
        ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_id' => $inactiveUnit->id,
            'conversion_to_base' => 2.0,
            'is_active' => false,
        ]);

        $activeProductUnits = ProductUnit::active()->get();

        $this->assertCount(1, $activeProductUnits);
    }

    /** @test */
    public function it_has_for_product_scope()
    {
        $anotherProduct = Product::create([
            'name' => 'Another Product',
            'sku' => 'TEST-002',
            'unit' => 'KG',
            'base_unit_id' => $this->unit->id,
            'purchase_price' => 200.00,
            'sale_price' => 250.00,
        ]);

        ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_id' => $this->unit->id,
            'conversion_to_base' => 1.0,
        ]);

        ProductUnit::create([
            'product_id' => $anotherProduct->id,
            'unit_id' => $this->unit->id,
            'conversion_to_base' => 1.0,
        ]);

        $productUnits = ProductUnit::forProduct($this->product->id)->get();

        $this->assertCount(1, $productUnits);
        $this->assertEquals($this->product->id, $productUnits->first()->product_id);
    }

    /** @test */
    public function it_has_base_unit_scope()
    {
        ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_id' => $this->unit->id,
            'conversion_to_base' => 1.0,
        ]);

        $secondaryUnit = Unit::create(['name' => 'Bag', 'abbreviation' => 'Bag', 'category' => 'packaging']);
        ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_id' => $secondaryUnit->id,
            'conversion_to_base' => 50.0,
        ]);

        $baseUnits = ProductUnit::baseUnit()->get();

        $this->assertCount(1, $baseUnits);
        $this->assertTrue($baseUnits->first()->isBaseUnit());
    }

    /** @test */
    public function it_belongs_to_product()
    {
        $productUnit = ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_id' => $this->unit->id,
            'conversion_to_base' => 1.0,
        ]);

        $this->assertInstanceOf(Product::class, $productUnit->product);
        $this->assertEquals($this->product->id, $productUnit->product->id);
    }

    /** @test */
    public function it_belongs_to_unit()
    {
        $productUnit = ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_id' => $this->unit->id,
            'conversion_to_base' => 1.0,
        ]);

        $this->assertInstanceOf(Unit::class, $productUnit->unit);
        $this->assertEquals($this->unit->id, $productUnit->unit->id);
    }

    /** @test */
    public function it_casts_conversion_to_base_to_decimal()
    {
        $productUnit = ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_id' => $this->unit->id,
            'conversion_to_base' => '50.1234',
        ]);

        $this->assertEquals('50.1234', $productUnit->conversion_to_base);
    }
}
