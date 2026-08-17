<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates test users with different roles for development/testing.
     * DO NOT run in production!
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->error('This seeder should not be run in production!');
            return;
        }

        $testUsers = [
            [
                'name' => 'Admin User',
                'email' => 'admin@test.com',
                'password' => Hash::make('password'),
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'role' => 'admin',
            ],
        ];

        foreach ($testUsers as $userData) {
            $roleSlug = $userData['role'];
            unset($userData['role']);

            // Check if user already exists
            $existingUser = User::where('email', $userData['email'])->first();

            if ($existingUser) {
                $this->command->info("User already exists: {$userData['email']}");
                continue;
            }

            // Create user
            $user = User::create($userData);

            // Assign role
            $role = Role::where('slug', $roleSlug)->first();
            if ($role) {
                $user->assignRole($role);
                $this->command->info("Created user: {$userData['email']} with role: {$role->name}");
            }
        }

        $this->command->info('');
        $this->command->info('Test users created successfully!');
        $this->command->warn('Default password for all test users: password');
        $this->command->warn('⚠️  These are test users - DO NOT use in production!');
    }
}
