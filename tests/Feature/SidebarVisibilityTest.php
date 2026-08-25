<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_transfer_link_is_hidden_when_only_one_active_warehouse_exists(): void
    {
        $user = User::factory()->create();

        $role = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'System administrator',
            'is_super_admin' => true,
        ]);

        $user->roles()->attach($role->id);

        Warehouse::create([
            'name' => 'Main Warehouse',
            'code' => 'WH-001',
            'type' => Warehouse::TYPE_MAIN,
            'address' => 'Head office',
            'status' => Warehouse::STATUS_ACTIVE,
            'is_default' => true,
        ]);

        $this->actingAs($user);

        $html = view('layouts.admin')->render();

        $this->assertStringNotContainsString('Stock Transfers', $html);

        Warehouse::create([
            'name' => 'Branch Warehouse',
            'code' => 'WH-002',
            'type' => Warehouse::TYPE_BRANCH,
            'address' => 'Branch office',
            'status' => Warehouse::STATUS_ACTIVE,
            'is_default' => false,
        ]);

        $html = view('layouts.admin')->render();

        $this->assertStringContainsString('Stock Transfers', $html);
    }

    public function test_inventory_summary_cards_link_to_filtered_inventory_results(): void
    {
        $html = view('admin.inventory.index', [
            'inventory' => collect(),
            'warehouses' => collect(),
            'products' => collect(),
            'stats' => [
                'total_items' => 12,
                'total_quantity' => 430,
                'low_stock' => 4,
                'out_of_stock' => 2,
            ],
        ])->render();

        $this->assertStringContainsString(route('admin.inventory.index', ['stock_status' => 'in_stock']), $html);
        $this->assertStringContainsString(route('admin.inventory.index', ['stock_status' => 'low_stock']), $html);
        $this->assertStringContainsString(route('admin.inventory.index', ['stock_status' => 'out_of_stock']), $html);
    }
}
