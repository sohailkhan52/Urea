<?php

namespace Tests\Unit\Models;

use App\Models\Unit;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_unit()
    {
        $unit = Unit::create([
            'name' => 'Kilogram',
            'abbreviation' => 'KG',
            'category' => 'weight',
            'is_active' => true,
            'description' => 'Standard unit of mass',
        ]);

        $this->assertDatabaseHas('units', [
            'name' => 'Kilogram',
            'abbreviation' => 'KG',
            'category' => 'weight',
        ]);
    }

    /** @test */
    public function it_enforces_unique_name()
    {
        Unit::create([
            'name' => 'Kilogram',
            'abbreviation' => 'KG',
            'category' => 'weight',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Unit::create([
            'name' => 'Kilogram',
            'abbreviation' => 'KG2',
            'category' => 'weight',
        ]);
    }

    /** @test */
    public function it_has_category_label_attribute()
    {
        $unit = Unit::create([
            'name' => 'Kilogram',
            'abbreviation' => 'KG',
            'category' => 'weight',
        ]);

        $this->assertEquals('Weight', $unit->category_label);

        $volumeUnit = Unit::create([
            'name' => 'Litre',
            'abbreviation' => 'L',
            'category' => 'volume',
        ]);

        $this->assertEquals('Volume', $volumeUnit->category_label);
    }

    /** @test */
    public function it_has_display_name_attribute()
    {
        $unit = Unit::create([
            'name' => 'Kilogram',
            'abbreviation' => 'KG',
            'category' => 'weight',
        ]);

        $this->assertEquals('Kilogram (KG)', $unit->display_name);
    }

    /** @test */
    public function it_has_active_scope()
    {
        Unit::create(['name' => 'Active Unit', 'abbreviation' => 'AU', 'category' => 'count', 'is_active' => true]);
        Unit::create(['name' => 'Inactive Unit', 'abbreviation' => 'IU', 'category' => 'count', 'is_active' => false]);

        $activeUnits = Unit::active()->get();

        $this->assertCount(1, $activeUnits);
        $this->assertEquals('Active Unit', $activeUnits->first()->name);
    }

    /** @test */
    public function it_has_by_category_scope()
    {
        Unit::create(['name' => 'Kilogram', 'abbreviation' => 'KG', 'category' => 'weight']);
        Unit::create(['name' => 'Litre', 'abbreviation' => 'L', 'category' => 'volume']);
        Unit::create(['name' => 'Piece', 'abbreviation' => 'PC', 'category' => 'count']);

        $weightUnits = Unit::byCategory('weight')->get();

        $this->assertCount(1, $weightUnits);
        $this->assertEquals('Kilogram', $weightUnits->first()->name);
    }

    /** @test */
    public function it_has_product_units_relationship()
    {
        $unit = Unit::create(['name' => 'Kilogram', 'abbreviation' => 'KG', 'category' => 'weight']);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $unit->productUnits());
    }

    /** @test */
    public function it_has_products_as_base_unit_relationship()
    {
        $unit = Unit::create(['name' => 'Kilogram', 'abbreviation' => 'KG', 'category' => 'weight']);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $unit->productsAsBaseUnit());
    }

    /** @test */
    public function it_casts_is_active_to_boolean()
    {
        $unit = Unit::create([
            'name' => 'Kilogram',
            'abbreviation' => 'KG',
            'category' => 'weight',
            'is_active' => 1,
        ]);

        $this->assertIsBool($unit->is_active);
        $this->assertTrue($unit->is_active);
    }

    /** @test */
    public function it_defaults_is_active_to_true()
    {
        $unit = Unit::create([
            'name' => 'Kilogram',
            'abbreviation' => 'KG',
            'category' => 'weight',
        ]);

        $this->assertTrue($unit->is_active);
    }
}
