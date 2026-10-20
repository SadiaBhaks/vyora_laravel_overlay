<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderPlaced extends Mailable
{
    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'VYORA order '.$this->order->number.' — payment received');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.order-placed');
    }
}
