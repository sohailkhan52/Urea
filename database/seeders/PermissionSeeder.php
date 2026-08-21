<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User Management
            [
                'name' => 'View Users',
                'slug' => 'users.view',
                'group' => 'users',
                'description' => 'View list of users',
            ],
            [
                'name' => 'Create Users',
                'slug' => 'users.create',
                'group' => 'users',
                'description' => 'Create new users',
            ],
            [
                'name' => 'Update Users',
                'slug' => 'users.update',
                'group' => 'users',
                'description' => 'Edit existing users',
            ],
            [
                'name' => 'Delete Users',
                'slug' => 'users.delete',
                'group' => 'users',
                'description' => 'Delete users',
            ],

            // Company Management
            [
                'name' => 'View Companies',
                'slug' => 'companies.view',
                'group' => 'companies',
                'description' => 'View list of companies',
            ],
            [
                'name' => 'Create Companies',
                'slug' => 'companies.create',
                'group' => 'companies',
                'description' => 'Create new companies',
            ],
            [
                'name' => 'Update Companies',
                'slug' => 'companies.update',
                'group' => 'companies',
                'description' => 'Edit existing companies',
            ],
            [
                'name' => 'Delete Companies',
                'slug' => 'companies.delete',
                'group' => 'companies',
                'description' => 'Delete companies',
            ],

            // Product Management
            [
                'name' => 'View Products',
                'slug' => 'products.view',
                'group' => 'products',
                'description' => 'View list of products',
            ],
            [
                'name' => 'Create Products',
                'slug' => 'products.create',
                'group' => 'products',
                'description' => 'Create new products',
            ],
            [
                'name' => 'Update Products',
                'slug' => 'products.update',
                'group' => 'products',
                'description' => 'Edit existing products',
            ],
            [
                'name' => 'Delete Products',
                'slug' => 'products.delete',
                'group' => 'products',
                'description' => 'Delete products',
            ],

            // Warehouse Management
            [
                'name' => 'View Warehouses',
                'slug' => 'warehouses.view',
                'group' => 'warehouses',
                'description' => 'View list of warehouses',
            ],
            [
                'name' => 'Create Warehouses',
                'slug' => 'warehouses.create',
                'group' => 'warehouses',
                'description' => 'Create new warehouses',
            ],
            [
                'name' => 'Update Warehouses',
                'slug' => 'warehouses.update',
                'group' => 'warehouses',
                'description' => 'Edit existing warehouses',
            ],
            [
                'name' => 'Delete Warehouses',
                'slug' => 'warehouses.delete',
                'group' => 'warehouses',
                'description' => 'Delete warehouses',
            ],

            // Inventory Management
            [
                'name' => 'View Inventory',
                'slug' => 'inventory.view',
                'group' => 'inventory',
                'description' => 'View inventory dashboard and stock levels',
            ],
            [
                'name' => 'Manage Stock',
                'slug' => 'inventory.manage',
                'group' => 'inventory',
                'description' => 'Add, remove, adjust, and transfer stock',
            ],

            // Supplier Management
            [
                'name' => 'View Suppliers',
                'slug' => 'suppliers.view',
                'group' => 'suppliers',
                'description' => 'View list of suppliers',
            ],
            [
                'name' => 'Create Suppliers',
                'slug' => 'suppliers.create',
                'group' => 'suppliers',
                'description' => 'Create new suppliers',
            ],
            [
                'name' => 'Update Suppliers',
                'slug' => 'suppliers.update',
                'group' => 'suppliers',
                'description' => 'Edit existing suppliers',
            ],
            [
                'name' => 'Delete Suppliers',
                'slug' => 'suppliers.delete',
                'group' => 'suppliers',
                'description' => 'Delete suppliers',
            ],

            // Purchase Management
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

            // Customer Management
            [
                'name' => 'View Customers',
                'slug' => 'customers.view',
                'group' => 'customers',
                'description' => 'View list of customers',
            ],
            [
                'name' => 'Create Customers',
                'slug' => 'customers.create',
                'group' => 'customers',
                'description' => 'Create new customers',
            ],
            [
                'name' => 'Update Customers',
                'slug' => 'customers.update',
                'group' => 'customers',
                'description' => 'Edit existing customers',
            ],
            [
                'name' => 'Delete Customers',
                'slug' => 'customers.delete',
                'group' => 'customers',
                'description' => 'Delete customers',
            ],

            // Sales Management
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

            // Inventory Management
            [
                'name' => 'View Inventory',
                'slug' => 'inventory.view',
                'group' => 'inventory',
                'description' => 'View inventory levels',
            ],
            [
                'name' => 'Adjust Inventory',
                'slug' => 'inventory.adjust',
                'group' => 'inventory',
                'description' => 'Adjust inventory quantities',
            ],

            // Stock Transfers
            [
                'name' => 'View Transfers',
                'slug' => 'transfers.view',
                'group' => 'transfers',
                'description' => 'View stock transfers',
            ],
            [
                'name' => 'Create Transfers',
                'slug' => 'transfers.create',
                'group' => 'transfers',
                'description' => 'Create stock transfers',
            ],
            [
                'name' => 'Approve Transfers',
                'slug' => 'transfers.approve',
                'group' => 'transfers',
                'description' => 'Approve stock transfers',
            ],
            [
                'name' => 'Receive Transfers',
                'slug' => 'transfers.receive',
                'group' => 'transfers',
                'description' => 'Receive stock transfers',
            ],

            // Reports
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

            // Dealers
            [
                'name' => 'View Dealers',
                'slug' => 'dealers.view',
                'group' => 'dealers',
                'description' => 'View list of dealers',
            ],
            [
                'name' => 'Create Dealers',
                'slug' => 'dealers.create',
                'group' => 'dealers',
                'description' => 'Create new dealers',
            ],
            [
                'name' => 'Update Dealers',
                'slug' => 'dealers.update',
                'group' => 'dealers',
                'description' => 'Edit existing dealers',
            ],
            [
                'name' => 'Delete Dealers',
                'slug' => 'dealers.delete',
                'group' => 'dealers',
                'description' => 'Delete dealers',
            ],

            // Udhar Management (Credit/Outstanding)
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

            // Payables Management (Supplier Outstanding)
            [
                'name' => 'View Payables',
                'slug' => 'payables.view',
                'group' => 'payables',
                'description' => 'View Payables (supplier outstanding balance) management',
            ],
            [
                'name' => 'Record Payables Payments',
                'slug' => 'payables.create',
                'group' => 'payables',
                'description' => 'Record payments for supplier payables',
            ],
            [
                'name' => 'Manage Payables',
                'slug' => 'payables.manage',
                'group' => 'payables',
                'description' => 'Full management of payables',
            ],
            [
                'name' => 'Export Payables Reports',
                'slug' => 'payables.export',
                'group' => 'payables',
                'description' => 'Export payables reports',
            ],

            // Welcome Page Management
            [
                'name' => 'Manage Welcome Page',
                'slug' => 'welcome-page.manage',
                'group' => 'welcome-page',
                'description' => 'Manage welcome page content, settings, features, and workflow',
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
