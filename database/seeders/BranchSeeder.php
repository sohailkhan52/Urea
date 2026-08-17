<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Head Office',
                'code' => 'HO',
                'phone' => '+92-51-111-222-333',
                'email' => 'headoffice@urea.com',
                'address' => 'F-7 Markaz, Blue Area, Islamabad',
                'city' => 'Islamabad',
                'status' => 'active',
            ],
            [
                'name' => 'Karachi Branch',
                'code' => 'KHI',
                'phone' => '+92-21-111-222-333',
                'email' => 'karachi@urea.com',
                'address' => 'Clifton Block 8, Main Boulevard, Karachi',
                'city' => 'Karachi',
                'status' => 'active',
            ],
            [
                'name' => 'Lahore Branch',
                'code' => 'LHR',
                'phone' => '+92-42-111-222-333',
                'email' => 'lahore@urea.com',
                'address' => 'Gulberg III, Main Boulevard, Lahore',
                'city' => 'Lahore',
                'status' => 'active',
            ],
            [
                'name' => 'Faisalabad Branch',
                'code' => 'FSD',
                'phone' => '+92-41-111-222-333',
                'email' => 'faisalabad@urea.com',
                'address' => 'Susan Road, Faisalabad',
                'city' => 'Faisalabad',
                'status' => 'active',
            ],
            [
                'name' => 'Multan Branch',
                'code' => 'MLT',
                'phone' => '+92-61-111-222-333',
                'email' => 'multan@urea.com',
                'address' => 'Bosan Road, Multan',
                'city' => 'Multan',
                'status' => 'active',
            ],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }

        $this->command->info('Branches seeded successfully!');
    }
}
