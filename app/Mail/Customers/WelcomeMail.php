<?php

namespace App\Mail\Customers;

use App\Helpers\CompanyHelper;
use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Customer $customer
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.address'),
            replyTo: config('mail.from.address'),
            subject: "Welcome to " . CompanyHelper::getCompanyName() . " - Customer Account Created",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.customers.welcome',
            with: [
                'customer' => $this->customer,
                'companyName' => CompanyHelper::getCompanyName(),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
