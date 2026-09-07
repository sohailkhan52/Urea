<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixCustomerPaymentAccountType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:customer-payment-account-type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix customer payments by setting account_type and account_family_id based on sale data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing customer payments account type and family id...');

        try {
            $result = DB::statement('
                UPDATE customer_payments cp
                INNER JOIN sales s ON cp.sale_id = s.id
                SET 
                    cp.account_type = s.udhar_account_type,
                    cp.account_family_id = s.family_id
                WHERE cp.account_type IS NULL OR (cp.account_family_id IS NULL AND s.family_id IS NOT NULL) OR (cp.account_family_id IS NOT NULL AND s.family_id IS NULL)
            ');

            $this->info('✓ Customer payments updated successfully');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
