<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Console\Command;

class SetupExpenseFeature extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:expenses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup Expense Management feature - create permissions and assign to roles';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== Setting up Expense Management Feature ===');

        try {
            // Step 1: Create expense permissions
            $this->info('Creating expense permissions...');
            $expensePermissions = [
                ['name' => 'View Expenses', 'slug' => 'expenses.view', 'group' => 'expenses', 'description' => 'View list of expenses'],
                ['name' => 'Create Expenses', 'slug' => 'expenses.create', 'group' => 'expenses', 'description' => 'Create new expenses'],
                ['name' => 'Edit Expenses', 'slug' => 'expenses.edit', 'group' => 'expenses', 'description' => 'Edit existing expenses'],
                ['name' => 'Delete Expenses', 'slug' => 'expenses.delete', 'group' => 'expenses', 'description' => 'Delete expenses'],
            ];

            foreach ($expensePermissions as $perm) {
                Permission::firstOrCreate(['slug' => $perm['slug']], $perm);
            }
            $this->line('✓ Expense permissions created');

            // Step 2: Assign permissions to Admin role
            $this->info('Assigning permissions to Admin role...');
            $adminRole = Role::where('slug', 'admin')->first();
            
            if ($adminRole) {
                $permissionSlugs = ['expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete'];
                $permissions = Permission::whereIn('slug', $permissionSlugs)->get();
                $adminRole->permissions()->syncWithoutDetaching($permissions);
                $this->line('✓ Permissions assigned to Admin role');
            } else {
                $this->warn('Admin role not found. Please ensure roles are seeded first.');
            }

            $this->info('=== Setup Complete ===');
            $this->line('Expense Management feature is ready!');
            $this->line('Please refresh your browser to see the changes.');

            return 0;
        } catch (\Exception $e) {
            $this->error('Setup failed: ' . $e->getMessage());
            return 1;
        }
    }
}
