<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $dataType;
    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct($dataType, $data)
    {
        $this->dataType = $dataType;
        $this->data = $data;
        
        // Route to default queue
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Illuminate\Support\Facades\Log::info("Processing {$this->dataType} data", [
            'data_count' => is_array($this->data) ? count($this->data) : 1,
        ]);

        // Process data logic here
        // Example:
        // foreach ($this->data as $item) {
        //     // Process each item
        // }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Exception $exception): void
    {
        \Illuminate\Support\Facades\Log::error("Data processing job failed", [
            'data_type' => $this->dataType,
            'error' => $exception->getMessage(),
        ]);
    }
}
