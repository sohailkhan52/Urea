<?php

namespace App\Console\Commands;

use App\Models\CustomerPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupReturnPaymentRecords extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cleanup:return-payment-records {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     */
    protected $description = 'Delete incorrect return_adjustment and return_credit payment records created during the bug period. These should not exist as separate payments.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('🔍 Scanning for incorrect return payment records...');
        $this->newLine();
        
        // Count records that will be deleted
        $adjustmentCount = CustomerPayment::where('payment_method', 'return_adjustment')->count();
        $creditCount = CustomerPayment::where('payment_method', 'return_credit')->count();
        
        $totalCount = $adjustmentCount + $creditCount;
        
        if ($totalCount === 0) {
            $this->info('✅ No incorrect payment records found. The bug has been fixed!');
            return Command::SUCCESS;
        }
        
        $this->warn("Found {$totalCount} incorrect payment records to delete:");
        $this->line("  - return_adjustment: {$adjustmentCount}");
        $this->line("  - return_credit: {$creditCount}");
        $this->newLine();
        
        if ($dryRun) {
            $this->info('🔒 DRY RUN MODE - No records were actually deleted.');
            $this->line('Run without --dry-run flag to actually delete these records.');
            return Command::SUCCESS;
        }
        
        if (!$this->confirm('Are you sure you want to delete these records?', false)) {
            $this->info('Cancelled. No records were deleted.');
            return Command::SUCCESS;
        }
        
        try {
            DB::beginTransaction();
            
            // Delete return_adjustment and return_credit payments
            CustomerPayment::whereIn('payment_method', ['return_adjustment', 'return_credit'])
                ->delete();
            
            DB::commit();
            
            $this->info("✅ Successfully deleted {$totalCount} incorrect payment records.");
            $this->line('Outstanding amounts will now be calculated correctly using the formula:');
            $this->line('Outstanding = Sale Total - Original Paid - Total Returns');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error deleting records: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
