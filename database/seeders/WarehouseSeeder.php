<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get branches
        $headOffice = Branch::where('code', 'HO')->first();
        $karachi = Branch::where('code', 'KHI')->first();
        $lahore = Branch::where('code', 'LHR')->first();
        $faisalabad = Branch::where('code', 'FSD')->first();

        // Get a manager (Super Admin or first active user)
        $manager = User::where('status', 'active')->first();

        if (!$headOffice || !$karachi || !$lahore) {
            $this->command->error('Please seed branches first!');
            return;
        }

        $warehouses = [
            // Main Warehouse
            [
                'branch_id' => null, // Main warehouse not tied to specific branch
                'name' => 'Central Main Warehouse',
                'code' => 'WH-MAIN-001',
                'type' => 'main_warehouse',
                'address' => 'Industrial Area, I-9, Islamabad',
                'manager_id' => $manager?->id,
                'status' => 'active',
            ],

            // Head Office Warehouses
            [
                'branch_id' => $headOffice->id,
                'name' => 'Islamabad Branch Warehouse',
                'code' => 'WH-ISB-001',
                'type' => 'branch_warehouse',
                'address' => 'G-11 Markaz, Islamabad',
                'manager_id' => null,
                'status' => 'active',
            ],
            [
                'branch_id' => $headOffice->id,
                'name' => 'Islamabad Store 1',
                'code' => 'ST-ISB-001',
                'type' => 'store',
                'address' => 'F-10 Markaz, Islamabad',
                'manager_id' => null,
                'status' => 'active',
            ],

            // Karachi Warehouses
            [
                'branch_id' => $karachi->id,
                'name' => 'Karachi Branch Warehouse',
                'code' => 'WH-KHI-001',
                'type' => 'branch_warehouse',
                'address' => 'Port Qasim Industrial Area, Karachi',
                'manager_id' => null,
                'status' => 'active',
            ],
            [
                'branch_id' => $karachi->id,
                'name' => 'Karachi Store - Clifton',
                'code' => 'ST-KHI-001',
                'type' => 'store',
                'address' => 'Clifton Block 2, Karachi',
                'manager_id' => null,
                'status' => 'active',
            ],
            [
                'branch_id' => $karachi->id,
                'name' => 'Karachi Store - Saddar',
                'code' => 'ST-KHI-002',
                'type' => 'store',
                'address' => 'Saddar, Karachi',
                'manager_id' => null,
                'status' => 'active',
            ],

            // Lahore Warehouses
            [
                'branch_id' => $lahore->id,
                'name' => 'Lahore Branch Warehouse',
                'code' => 'WH-LHR-001',
                'type' => 'branch_warehouse',
                'address' => 'Sundar Industrial Estate, Lahore',
                'manager_id' => null,
                'status' => 'active',
            ],
            [
                'branch_id' => $lahore->id,
                'name' => 'Lahore Store - Gulberg',
                'code' => 'ST-LHR-001',
                'type' => 'store',
                'address' => 'Gulberg II, Lahore',
                'manager_id' => null,
                'status' => 'active',
            ],

            // Faisalabad Warehouses
            [
                'branch_id' => $faisalabad->id,
                'name' => 'Faisalabad Branch Warehouse',
                'code' => 'WH-FSD-001',
                'type' => 'branch_warehouse',
                'address' => 'Chak Jhumra Road, Faisalabad',
                'manager_id' => null,
                'status' => 'active',
            ],
            [
                'branch_id' => $faisalabad->id,
                'name' => 'Faisalabad Store',
                'code' => 'ST-FSD-001',
                'type' => 'store',
                'address' => 'Susan Road, Faisalabad',
                'manager_id' => null,
                'status' => 'inactive',
            ],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::create($warehouse);
        }

        $this->command->info('Warehouses seeded successfully!');
    }
}
