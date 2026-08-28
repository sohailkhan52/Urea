<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reportType;
    protected $userId;
    protected $parameters;

    /**
     * Create a new job instance.
     */
    public function __construct($reportType, $userId, $parameters = [])
    {
        $this->reportType = $reportType;
        $this->userId = $userId;
        $this->parameters = $parameters;
        
        // Route to reports queue
        $this->onQueue('reports');
        
        // Set higher timeout for report generation
        $this->timeout = 300;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Illuminate\Support\Facades\Log::info("Generating {$this->reportType} report for user {$this->userId}", [
            'parameters' => $this->parameters,
        ]);

        // Generate report logic here
        // Example:
        // $report = Report::generate($this->reportType, $this->parameters);
        // Notify user that report is ready
    }
}
