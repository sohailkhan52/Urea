<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use Illuminate\Console\Command;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:superadmin {--name=sohail} {--email=sohailkhan52117@gmail.com}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a superadmin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->option('name');
        $email = $this->option('email');

        try {
            // Check if user already exists
            $existingUser = User::where('email', $email)->first();
            
            if ($existingUser) {
                $this->info("User with email {$email} already exists.");
                $this->info("User ID: {$existingUser->id}");
                $this->info("Current name: {$existingUser->name}");
                
                // Update name if different
                if ($existingUser->name !== $name) {
                    $existingUser->update(['name' => $name]);
                    $this->info("✓ Updated name to '{$name}'");
                }
                
                $user = $existingUser;
            } else {
                // Create new user
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => bcrypt('password'), // Default password
                    'status' => 'active',
                ]);
                
                $this->info("✓ User created successfully");
                $this->info("  ID: {$user->id}");
                $this->info("  Name: {$user->name}");
                $this->info("  Email: {$user->email}");
            }
            
            // Get or create superadmin role
            $superAdminRole = Role::where('slug', 'superadmin')->first();
            
            if (!$superAdminRole) {
                $this->info("Creating superadmin role...");
                $superAdminRole = Role::create([
                    'name' => 'Super Admin',
                    'slug' => 'superadmin',
                    'description' => 'Super Administrator with full access',
                    'is_super_admin' => true,
                ]);
                $this->info("✓ Superadmin role created");
            }
            
            // Assign role to user
            $user->assignRole($superAdminRole);
            
            $this->info("✓ Superadmin role assigned to user");
            
            $this->info("\n=== Summary ===");
            $this->info("User: {$user->name} ({$user->email})");
            $this->info("Role: {$superAdminRole->name}");
            $this->info("Status: Active");
            $this->info("\n✓ Setup completed successfully!");
            
        } catch (\Exception $e) {
            $this->error("✗ Error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
