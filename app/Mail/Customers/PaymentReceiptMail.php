<?php

namespace App\Mail\Customers;

use App\Helpers\CompanyHelper;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Payment $payment
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.address'),
            replyTo: config('mail.from.address'),
            subject: CompanyHelper::getCompanyName() . " - Payment Receipt",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.customers.payment-receipt',
            with: [
                'payment' => $this->payment,
                'companyName' => CompanyHelper::getCompanyName(),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
