<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;

class CreateSuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or get superadmin role
        $superAdminRole = Role::firstOrCreate(
            ['slug' => 'superadmin'],
            [
                'name' => 'Super Admin',
                'description' => 'Super Administrator with full access',
                'is_super_admin' => true,
            ]
        );

        // Create or update superadmin user
        $user = User::firstOrCreate(
            ['email' => 'sohailkhan52117@gmail.com'],
            [
                'name' => 'sohail',
                'password' => bcrypt('password'),
                'status' => 'active',
            ]
        );

        // Update name if different (in case user already existed)
        if ($user->name !== 'sohail') {
            $user->update(['name' => 'sohail']);
        }

        // Assign superadmin role
        $user->assignRole($superAdminRole);

        $this->command->info('✓ Superadmin user created/updated successfully!');
        $this->command->info("  User: {$user->name} ({$user->email})");
        $this->command->info("  Role: {$superAdminRole->name}");
    }
}
