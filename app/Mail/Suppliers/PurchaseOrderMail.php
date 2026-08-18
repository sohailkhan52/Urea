<?php

namespace App\Mail\Suppliers;

use App\Models\Purchase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PurchaseOrderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Purchase $purchase
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.address'),
            replyTo: config('mail.from.address'),
            subject: "Purchase Order Confirmation - {$this->purchase->purchase_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.suppliers.purchase-order',
            with: [
                'purchase' => $this->purchase,
                'supplier' => $this->purchase->supplier,
                'items' => $this->purchase->items,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
