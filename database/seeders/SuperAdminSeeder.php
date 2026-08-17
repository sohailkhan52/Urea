<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin from environment variables or use defaults for development
        $superAdmin = [
            'name' => env('SUPER_ADMIN_NAME', 'Super Admin'),
            'email' => env('SUPER_ADMIN_EMAIL', 'admin@example.com'),
            'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', 'password')),
            'phone' => env('SUPER_ADMIN_PHONE', null),
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ];

        // Check if user already exists
        $existingUser = User::where('email', $superAdmin['email'])->first();

        if ($existingUser) {
            $this->command->info('Super Admin user already exists: ' . $superAdmin['email']);
            
            // Ensure super admin role is assigned
            $superAdminRole = Role::where('slug', 'super-admin')->first();
            if ($superAdminRole && !$existingUser->hasRole('super-admin')) {
                $existingUser->assignRole($superAdminRole);
                $this->command->info('Super Admin role assigned to existing user.');
            }
            
            return;
        }

        // Create the super admin user
        $user = User::create($superAdmin);

        // Assign Super Admin role
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        if ($superAdminRole) {
            $user->assignRole($superAdminRole);
            $this->command->info('Super Admin role assigned.');
        }

        $this->command->info('Super Admin user created successfully!');
        $this->command->info('Email: ' . $superAdmin['email']);
        
        if (app()->environment('local', 'development')) {
            $this->command->warn('Password: ' . env('SUPER_ADMIN_PASSWORD', 'password'));
            $this->command->warn('⚠️  Change this password immediately in production!');
        }
    }
}
