<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only run if permissions table exists
        if (!Schema::hasTable('permissions')) {
            return;
        }

        // Check if permissions already exist
        $permissions = [
            ['name' => 'View Categories', 'slug' => 'categories.view', 'group' => 'categories', 'description' => 'View all categories'],
            ['name' => 'Create Categories', 'slug' => 'categories.create', 'group' => 'categories', 'description' => 'Create new categories'],
            ['name' => 'Update Categories', 'slug' => 'categories.update', 'group' => 'categories', 'description' => 'Edit categories'],
            ['name' => 'Delete Categories', 'slug' => 'categories.delete', 'group' => 'categories', 'description' => 'Delete categories'],
        ];

        foreach ($permissions as $permission) {
            if (!DB::table('permissions')->where('slug', $permission['slug'])->exists()) {
                DB::table('permissions')->insert($permission);
            }
        }

        // Assign permissions to super admin role if role_permissions table exists
        if (Schema::hasTable('role_permissions') && Schema::hasTable('roles')) {
            $superAdminRole = DB::table('roles')->where('is_super_admin', true)->first();
            if ($superAdminRole) {
                $categoryPermissions = DB::table('permissions')
                    ->where('group', 'categories')
                    ->pluck('id');
                
                foreach ($categoryPermissions as $permissionId) {
                    if (!DB::table('role_permissions')->where('role_id', $superAdminRole->id)->where('permission_id', $permissionId)->exists()) {
                        DB::table('role_permissions')->insert([
                            'role_id' => $superAdminRole->id,
                            'permission_id' => $permissionId,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->where('group', 'categories')->delete();
        }
    }
};
