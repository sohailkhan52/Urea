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
        // Seed in proper order: permissions -> roles -> users -> branches -> warehouses -> suppliers -> customers
        // PHASE 1: Removed Company, Category, Product, StockTransfer, WelcomePage seeders
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            SuperAdminSeeder::class,
            TestUsersSeeder::class,
            BranchSeeder::class,
            WarehouseSeeder::class,
            SupplierSeeder::class,
            CustomerSeeder::class,
            SalesSeeder::class,
            // Note: Purchase and PurchaseItem records are created through PurchaseService
            // Note: Stock movements are created through StockService and SalesService
        ]);
    }
}
