<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * PHASE 1: Removed permissions for User, Company, Category, Product, Warehouse, Inventory, Customers, Expenses, Stock Transfers, Stock Requests, Welcome Page management
     */
    public function run(): void
    {
        $permissions = [
            // Purchase Management - KEPT
            [
                'name' => 'View Purchases',
                'slug' => 'purchases.view',
                'group' => 'purchases',
                'description' => 'View list of purchases',
            ],
            [
                'name' => 'Create Purchases',
                'slug' => 'purchases.create',
                'group' => 'purchases',
                'description' => 'Create new purchases',
            ],
            [
                'name' => 'Update Purchases',
                'slug' => 'purchases.update',
                'group' => 'purchases',
                'description' => 'Edit existing purchases',
            ],
            [
                'name' => 'Approve Purchases',
                'slug' => 'purchases.approve',
                'group' => 'purchases',
                'description' => 'Approve purchase orders',
            ],
            [
                'name' => 'Cancel Purchases',
                'slug' => 'purchases.cancel',
                'group' => 'purchases',
                'description' => 'Cancel purchase orders',
            ],

            // Sales Management - KEPT
            [
                'name' => 'View Sales',
                'slug' => 'sales.view',
                'group' => 'sales',
                'description' => 'View list of sales',
            ],
            [
                'name' => 'Create Sales',
                'slug' => 'sales.create',
                'group' => 'sales',
                'description' => 'Create new sales',
            ],
            [
                'name' => 'Update Sales',
                'slug' => 'sales.update',
                'group' => 'sales',
                'description' => 'Edit existing sales',
            ],
            [
                'name' => 'Cancel Sales',
                'slug' => 'sales.cancel',
                'group' => 'sales',
                'description' => 'Cancel sales orders',
            ],
            [
                'name' => 'Approve Sales',
                'slug' => 'sales.approve',
                'group' => 'sales',
                'description' => 'Confirm and approve sales orders',
            ],

            // Reports - KEPT
            [
                'name' => 'View Reports',
                'slug' => 'reports.view',
                'group' => 'reports',
                'description' => 'View reports',
            ],
            [
                'name' => 'Export Reports',
                'slug' => 'reports.export',
                'group' => 'reports',
                'description' => 'Export reports to Excel/PDF',
            ],

            // Udhar Management (Credit/Outstanding) - KEPT
            [
                'name' => 'View Udhar',
                'slug' => 'udhar.view',
                'group' => 'udhar',
                'description' => 'View Udhar (outstanding balance) management',
            ],
            [
                'name' => 'Record Udhar Payments',
                'slug' => 'udhar.create',
                'group' => 'udhar',
                'description' => 'Record payments for outstanding Udhar',
            ],
            [
                'name' => 'Export Udhar Reports',
                'slug' => 'udhar.export',
                'group' => 'udhar',
                'description' => 'Export Udhar reports',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        $this->command->info('Permissions created successfully!');
    }
}
