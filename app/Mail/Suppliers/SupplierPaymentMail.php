<?php

namespace App\Mail\Suppliers;

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

    /**
     * Create a new message instance.
     */
    public function __construct(
        public PurchasePayment $payment
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
            subject: "Payment Confirmation - {$this->payment->payment_number}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $purchase = $this->payment->purchase;
        
        return new Content(
            view: 'mails.suppliers.payment-confirmation',
            with: [
                'payment' => $this->payment,
                'purchase' => $purchase,
                'supplier' => $purchase->supplier,
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
