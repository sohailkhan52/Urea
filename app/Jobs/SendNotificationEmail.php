<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $subject;
    protected $message;

    /**
     * Create a new job instance.
     */
    public function __construct($email, $subject, $message)
    {
        $this->email = $email;
        $this->subject = $subject;
        $this->message = $message;
        
        // Route to notifications queue
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Send notification email
        \Illuminate\Support\Facades\Log::info("Sending notification email to {$this->email}");
        
        // Mail::send('emails.notification', ['message' => $this->message], function ($message) {
        //     $message->to($this->email)
        //             ->subject($this->subject);
        // });
    }
}
