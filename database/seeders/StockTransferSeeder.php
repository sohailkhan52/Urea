<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class StockTransferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = Warehouse::all();
        $products = Product::where('status', 'active')->get();
        $users = User::where('status', 'active')->limit(3)->get();

        if ($warehouses->count() < 2 || $products->count() < 2 || $users->count() < 1) {
            return; // Skip if not enough data
        }

        // Sample Transfer 1: Draft
        $transfer1 = StockTransfer::create([
            'transfer_number' => 'TRF-' . date('Y') . '-00001',
            'source_warehouse_id' => $warehouses->first()->id,
            'destination_warehouse_id' => $warehouses->last()->id,
            'status' => 'draft',
            'transfer_date' => now()->addDays(3)->toDateString(),
            'notes' => 'Regular monthly transfer',
            'created_by' => $users->first()->id,
        ]);

        $transfer1->items()->create([
            'product_id' => $products[0]->id,
            'quantity' => 100,
            'received_quantity' => 0,
        ]);
        $transfer1->items()->create([
            'product_id' => $products[1]->id,
            'quantity' => 50,
            'received_quantity' => 0,
        ]);

        // Sample Transfer 2: Pending Approval
        $transfer2 = StockTransfer::create([
            'transfer_number' => 'TRF-' . date('Y') . '-00002',
            'source_warehouse_id' => $warehouses[0]->id,
            'destination_warehouse_id' => $warehouses[1]->id ?? $warehouses->first()->id,
            'status' => 'pending_approval',
            'transfer_date' => now()->addDays(2)->toDateString(),
            'notes' => 'Urgent transfer for stock replenishment',
            'created_by' => $users->first()->id,
        ]);

        $transfer2->items()->create([
            'product_id' => $products[0]->id,
            'quantity' => 200,
            'received_quantity' => 0,
        ]);

        // Sample Transfer 3: Approved
        $transfer3 = StockTransfer::create([
            'transfer_number' => 'TRF-' . date('Y') . '-00003',
            'source_warehouse_id' => $warehouses->first()->id,
            'destination_warehouse_id' => $warehouses->last()->id,
            'status' => 'approved',
            'transfer_date' => now()->addDays(1)->toDateString(),
            'notes' => 'Pre-approved transfer',
            'created_by' => $users->first()->id,
            'approved_by' => $users->get(1)?->id ?? $users->first()->id,
            'approved_at' => now()->subHours(2),
        ]);

        $transfer3->items()->create([
            'product_id' => $products[0]->id,
            'quantity' => 150,
            'received_quantity' => 0,
        ]);

        // Sample Transfer 4: Dispatched
        if ($warehouses->count() >= 2) {
            $transfer4 = StockTransfer::create([
                'transfer_number' => 'TRF-' . date('Y') . '-00004',
                'source_warehouse_id' => $warehouses[0]->id,
                'destination_warehouse_id' => $warehouses[1]->id,
                'status' => 'dispatched',
                'transfer_date' => now()->subDays(1)->toDateString(),
                'notes' => 'Dispatched yesterday',
                'created_by' => $users->first()->id,
                'approved_by' => $users->get(1)?->id ?? $users->first()->id,
                'dispatched_by' => $users->get(2)?->id ?? $users->first()->id,
                'approved_at' => now()->subDays(1),
                'dispatched_at' => now()->subHours(12),
            ]);

            $transfer4->items()->create([
                'product_id' => $products[0]->id,
                'quantity' => 75,
                'received_quantity' => 0,
            ]);
        }

        // Sample Transfer 5: In Transit
        if ($warehouses->count() >= 2) {
            $transfer5 = StockTransfer::create([
                'transfer_number' => 'TRF-' . date('Y') . '-00005',
                'source_warehouse_id' => $warehouses[0]->id,
                'destination_warehouse_id' => $warehouses[1]->id,
                'status' => 'in_transit',
                'transfer_date' => now()->subDays(2)->toDateString(),
                'notes' => 'In transit to destination',
                'created_by' => $users->first()->id,
                'approved_by' => $users->get(1)?->id ?? $users->first()->id,
                'dispatched_by' => $users->get(2)?->id ?? $users->first()->id,
                'approved_at' => now()->subDays(2),
                'dispatched_at' => now()->subDays(1),
                'in_transit_at' => now()->subHours(6),
            ]);

            $transfer5->items()->create([
                'product_id' => $products[0]->id,
                'quantity' => 60,
                'received_quantity' => 0,
            ]);
        }

        // Sample Transfer 6: Partially Received
        if ($warehouses->count() >= 2) {
            $transfer6 = StockTransfer::create([
                'transfer_number' => 'TRF-' . date('Y') . '-00006',
                'source_warehouse_id' => $warehouses[0]->id,
                'destination_warehouse_id' => $warehouses[1]->id,
                'status' => 'in_transit',
                'transfer_date' => now()->subDays(3)->toDateString(),
                'notes' => 'Partially received',
                'created_by' => $users->first()->id,
                'approved_by' => $users->get(1)?->id ?? $users->first()->id,
                'dispatched_by' => $users->get(2)?->id ?? $users->first()->id,
                'received_by' => $users->get(1)?->id ?? $users->first()->id,
                'approved_at' => now()->subDays(3),
                'dispatched_at' => now()->subDays(2),
                'in_transit_at' => now()->subDays(1),
                'received_at' => now()->subHours(3),
            ]);

            $item = $transfer6->items()->create([
                'product_id' => $products[0]->id,
                'quantity' => 100,
                'received_quantity' => 60,
            ]);
        }

        // Sample Transfer 7: Fully Received
        if ($warehouses->count() >= 2) {
            $transfer7 = StockTransfer::create([
                'transfer_number' => 'TRF-' . date('Y') . '-00007',
                'source_warehouse_id' => $warehouses[0]->id,
                'destination_warehouse_id' => $warehouses[1]->id,
                'status' => 'received',
                'transfer_date' => now()->subDays(5)->toDateString(),
                'notes' => 'Complete transfer received',
                'created_by' => $users->first()->id,
                'approved_by' => $users->get(1)?->id ?? $users->first()->id,
                'dispatched_by' => $users->get(2)?->id ?? $users->first()->id,
                'received_by' => $users->get(1)?->id ?? $users->first()->id,
                'approved_at' => now()->subDays(5),
                'dispatched_at' => now()->subDays(4),
                'in_transit_at' => now()->subDays(3),
                'received_at' => now()->subDays(2),
            ]);

            $transfer7->items()->create([
                'product_id' => $products[0]->id,
                'quantity' => 80,
                'received_quantity' => 80,
            ]);
        }
    }
}
