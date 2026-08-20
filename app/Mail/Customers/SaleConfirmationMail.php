<?php

namespace App\Mail\Customers;

use App\Helpers\CompanyHelper;
use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SaleConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Sale $sale
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.address'),
            replyTo: config('mail.from.address'),
            subject: CompanyHelper::getCompanyName() . " - Sale Confirmation - Invoice {$this->sale->invoice_number}",
        );
    }

    public function content(): Content
    {
        $customerName = $this->sale->customer?->name ?? $this->sale->walkin_customer_name ?? 'Valued Customer';
        
        return new Content(
            view: 'mails.customers.sale-confirmation',
            with: [
                'sale' => $this->sale,
                'customerName' => $customerName,
                'companyName' => CompanyHelper::getCompanyName(),
                'items' => $this->sale->items,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
