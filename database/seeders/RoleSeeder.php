<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define roles - Only Super Admin and Admin
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Has complete access to the entire system',
                'is_super_admin' => true,
                'permissions' => [], // Super admin gets all permissions automatically
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Has access to most features',
                'is_super_admin' => false,
                'permissions' => [
                    'users.view', 'users.create', 'users.update', 'users.delete',
                    'companies.view', 'companies.create', 'companies.update', 'companies.delete',
                    'products.view', 'products.create', 'products.update', 'products.delete',
                    'warehouses.view', 'warehouses.create', 'warehouses.update', 'warehouses.delete',
                    'inventory.view', 'inventory.manage', 'inventory.check', 'inventory.adjust',
                    'suppliers.view', 'suppliers.create', 'suppliers.update', 'suppliers.delete',
                    'customers.view', 'customers.create', 'customers.update', 'customers.delete',
                    'purchases.view', 'purchases.create', 'purchases.update', 'purchases.approve', 'purchases.cancel',
                    'sales.view', 'sales.create', 'sales.update', 'sales.approve', 'sales.cancel',
                    'transfers.view', 'transfers.create', 'transfers.approve', 'transfers.receive',
                    'udhar.view', 'udhar.create', 'udhar.export',
                    'reports.view',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            // Create or update role
            $role = Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                [
                    'name' => $roleData['name'],
                    'description' => $roleData['description'],
                    'is_super_admin' => $roleData['is_super_admin'],
                ]
            );

            // Assign permissions to role (if not super admin)
            if (!$roleData['is_super_admin'] && !empty($roleData['permissions'])) {
                $permissions = Permission::whereIn('slug', $roleData['permissions'])->get();
                $role->permissions()->sync($permissions);
            }

            $this->command->info("Role '{$role->name}' created with " . count($roleData['permissions']) . ' permissions.');
        }
    }
}
