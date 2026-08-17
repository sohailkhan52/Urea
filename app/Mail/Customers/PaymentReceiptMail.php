<?php

namespace App\Mail\Customers;

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

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Payment $payment
    ) {
        $this->queue = 'default';
        $this->delay = 0;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $sale = $this->payment->sale;
        return new Envelope(
            subject: "Payment Receipt - Invoice {$sale->invoice_number}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $sale = $this->payment->sale;
        $customerName = $sale->customer?->name ?? $sale->walkin_customer_name ?? 'Valued Customer';
        
        return new Content(
            view: 'mails.customers.payment-receipt',
            with: [
                'payment' => $this->payment,
                'sale' => $sale,
                'customerName' => $customerName,
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
