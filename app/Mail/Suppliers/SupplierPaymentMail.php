<?php

namespace App\Mail\Suppliers;

use App\Helpers\CompanyHelper;
use App\Models\PurchasePayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupplierPaymentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public PurchasePayment $payment
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.address'),
            replyTo: config('mail.from.address'),
            subject: CompanyHelper::getCompanyName() . " - Payment Confirmation",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.suppliers.payment-confirmation',
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
