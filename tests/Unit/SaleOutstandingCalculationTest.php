<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\SaleItem;

/**
 * Test cases for the fixed Sale Outstanding calculation bug
 * 
 * BUG: When a return was created, return_adjustment payments were being summed
 * into total_additional_payments, artificially inflating the "paid" amount.
 * 
 * FIX: return_adjustment and return_credit are NO LONGER created as payments.
 * Outstanding is calculated as: Sale Total - Original Paid - Total Returns
 */
class SaleOutstandingCalculationTest extends TestCase
{
    protected $customer;
    protected $warehouse;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->customer = Customer::factory()->create();
        $this->warehouse = Warehouse::factory()->create();
        $this->product = Product::factory()->create();
    }

    /**
     * TEST 1: Sale = 65,000, Paid = 50,000, Return = 14,950
     * Expected Outstanding = 50
     */
    public function test_partial_return_calculation()
    {
        $sale = Sale::factory()
            ->for($this->customer)
            ->for($this->warehouse)
            ->create([
                'total_amount' => 65000,
                'paid_amount' => 50000,
            ]);

        // Create sale item
        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'quantity' => 122,
            'unit_price' => 532.79,
            'total' => 65000,
        ]);

        // Confirm sale
        $sale->update(['status' => 'confirmed']);

        // Create return of 14,950
        $return = SaleReturn::create([
            'sale_id' => $sale->id,
            'customer_id' => $sale->customer_id,
            'warehouse_id' => $sale->warehouse_id,
            'return_number' => 'RET-20260101-0001',
            'return_date' => now(),
            'total_return_amount' => 14950,
            'status' => 'confirmed',
            'created_by' => 1,
        ]);

        // Verify calculation
        $sale->refresh();
        
        $this->assertEquals(65000, $sale->total_amount, 'Total amount should remain 65000');
        $this->assertEquals(50000, $sale->paid_amount, 'Original paid_amount should remain 50000');
        $this->assertEquals(14950, $sale->total_returned_amount, 'Total returned should be 14950');
        
        $expectedOutstanding = 65000 - 50000 - 14950;
        $this->assertEquals($expectedOutstanding, $sale->current_remaining_udhar, "Outstanding should be {$expectedOutstanding}, not -14,950");
    }

    /**
     * TEST 2: Full return
     * Sale = 65,000, Paid = 50,000, Return = 65,000
     * Expected Outstanding = -50,000 (customer credit)
     */
    public function test_full_return_calculation()
    {
        $sale = Sale::factory()
            ->for($this->customer)
            ->for($this->warehouse)
            ->create([
                'total_amount' => 65000,
                'paid_amount' => 50000,
            ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'quantity' => 122,
            'unit_price' => 532.79,
            'total' => 65000,
        ]);

        $sale->update(['status' => 'confirmed']);

        // Full return
        $return = SaleReturn::create([
            'sale_id' => $sale->id,
            'customer_id' => $sale->customer_id,
            'warehouse_id' => $sale->warehouse_id,
            'return_number' => 'RET-20260101-0002',
            'return_date' => now(),
            'total_return_amount' => 65000,
            'status' => 'confirmed',
            'created_by' => 1,
        ]);

        $sale->refresh();
        
        $this->assertEquals(65000, $sale->total_amount);
        $this->assertEquals(50000, $sale->paid_amount);
        $this->assertEquals(65000, $sale->total_returned_amount);
        
        $expectedOutstanding = 65000 - 50000 - 65000;
        $this->assertEquals($expectedOutstanding, $sale->current_remaining_udhar, 'Outstanding should be -50,000 (customer credit)');
    }

    /**
     * TEST 3: Multiple partial returns on same invoice
     * Sale = 65,000, Paid = 50,000
     * Return 1 = 14,950
     * Return 2 = 5,000
     * Expected: Total Returns = 19,950, Outstanding = -4,950
     */
    public function test_multiple_partial_returns()
    {
        $sale = Sale::factory()
            ->for($this->customer)
            ->for($this->warehouse)
            ->create([
                'total_amount' => 65000,
                'paid_amount' => 50000,
            ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'quantity' => 122,
            'unit_price' => 532.79,
            'total' => 65000,
        ]);

        $sale->update(['status' => 'confirmed']);

        // First return
        SaleReturn::create([
            'sale_id' => $sale->id,
            'customer_id' => $sale->customer_id,
            'warehouse_id' => $sale->warehouse_id,
            'return_number' => 'RET-20260101-0003',
            'return_date' => now(),
            'total_return_amount' => 14950,
            'status' => 'confirmed',
            'created_by' => 1,
        ]);

        // Second return
        SaleReturn::create([
            'sale_id' => $sale->id,
            'customer_id' => $sale->customer_id,
            'warehouse_id' => $sale->warehouse_id,
            'return_number' => 'RET-20260101-0004',
            'return_date' => now(),
            'total_return_amount' => 5000,
            'status' => 'confirmed',
            'created_by' => 1,
        ]);

        $sale->refresh();
        
        $this->assertEquals(65000, $sale->total_amount);
        $this->assertEquals(50000, $sale->paid_amount);
        $this->assertEquals(19950, $sale->total_returned_amount, 'Total returns should sum to 19,950');
        
        $expectedOutstanding = 65000 - 50000 - 19950;
        $this->assertEquals($expectedOutstanding, $sale->current_remaining_udhar, 'Outstanding should be -4,950');
    }

    /**
     * TEST 4: No payment, partial return
     * Sale = 65,000, Paid = 0, Return = 14,950
     * Expected Outstanding = 50,050
     */
    public function test_unpaid_sale_with_return()
    {
        $sale = Sale::factory()
            ->for($this->customer)
            ->for($this->warehouse)
            ->create([
                'total_amount' => 65000,
                'paid_amount' => 0,
            ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'quantity' => 122,
            'unit_price' => 532.79,
            'total' => 65000,
        ]);

        $sale->update(['status' => 'confirmed']);

        SaleReturn::create([
            'sale_id' => $sale->id,
            'customer_id' => $sale->customer_id,
            'warehouse_id' => $sale->warehouse_id,
            'return_number' => 'RET-20260101-0005',
            'return_date' => now(),
            'total_return_amount' => 14950,
            'status' => 'confirmed',
            'created_by' => 1,
        ]);

        $sale->refresh();
        
        $this->assertEquals(65000, $sale->total_amount);
        $this->assertEquals(0, $sale->paid_amount);
        $this->assertEquals(14950, $sale->total_returned_amount);
        
        $expectedOutstanding = 65000 - 0 - 14950;
        $this->assertEquals($expectedOutstanding, $sale->current_remaining_udhar, 'Outstanding should be 50,050');
    }

    /**
     * TEST 5: Fully paid sale with partial return
     * Sale = 65,000, Paid = 65,000, Return = 14,950
     * Expected Outstanding = -14,950 (customer credit)
     */
    public function test_fully_paid_sale_with_return()
    {
        $sale = Sale::factory()
            ->for($this->customer)
            ->for($this->warehouse)
            ->create([
                'total_amount' => 65000,
                'paid_amount' => 65000,
            ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'quantity' => 122,
            'unit_price' => 532.79,
            'total' => 65000,
        ]);

        $sale->update(['status' => 'confirmed']);

        SaleReturn::create([
            'sale_id' => $sale->id,
            'customer_id' => $sale->customer_id,
            'warehouse_id' => $sale->warehouse_id,
            'return_number' => 'RET-20260101-0006',
            'return_date' => now(),
            'total_return_amount' => 14950,
            'status' => 'confirmed',
            'created_by' => 1,
        ]);

        $sale->refresh();
        
        $this->assertEquals(65000, $sale->total_amount);
        $this->assertEquals(65000, $sale->paid_amount);
        $this->assertEquals(14950, $sale->total_returned_amount);
        
        $expectedOutstanding = 65000 - 65000 - 14950;
        $this->assertEquals($expectedOutstanding, $sale->current_remaining_udhar, 'Outstanding should be -14,950');
    }
}
