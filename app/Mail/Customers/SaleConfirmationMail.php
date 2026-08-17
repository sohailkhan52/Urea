<?php

namespace App\Mail\Customers;

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

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Sale $sale
    ) {
        $this->queue = 'default';
        $this->delay = 0;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Sale Confirmation - Invoice {$this->sale->invoice_number}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $customerName = $this->sale->customer?->name ?? $this->sale->walkin_customer_name ?? 'Valued Customer';
        
        return new Content(
            view: 'mails.customers.sale-confirmation',
            with: [
                'sale' => $this->sale,
                'customerName' => $customerName,
                'items' => $this->sale->items,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
