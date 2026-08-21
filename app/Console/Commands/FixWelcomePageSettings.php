<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixWelcomePageSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'welcome-page:setup';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Setup the welcome page settings table with company_description column';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking welcome_page_settings table...');

        // Check if table exists
        if (!Schema::hasTable('welcome_page_settings')) {
            $this->error('Table welcome_page_settings does not exist!');
            return 1;
        }

        $this->info('✓ Table exists');

        // Check if company_description column exists
        if (Schema::hasColumn('welcome_page_settings', 'company_description')) {
            $this->info('✓ company_description column already exists');
            return 0;
        }

        // Add the column
        try {
            Schema::table('welcome_page_settings', function ($table) {
                $table->longText('company_description')->nullable()->after('company_logo');
            });

            $this->info('✓ Successfully added company_description column');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error adding column: ' . $e->getMessage());
            return 1;
        }
    }
}
