<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed in proper order: permissions -> roles -> users -> companies -> categories -> products -> branches -> warehouses -> suppliers -> customers -> sales -> transfers
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            SuperAdminSeeder::class,
            TestUsersSeeder::class,
            CompanySeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            BranchSeeder::class,
            WarehouseSeeder::class,
            SupplierSeeder::class,
            CustomerSeeder::class,
            SalesSeeder::class,
            StockTransferSeeder::class,
            // Note: Purchase and PurchaseItem records are created through PurchaseService
            // Note: Stock movements are created through StockService and SalesService
        ]);
    }
}
