<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactReceived extends Mailable
{
    public function __construct(public ContactMessage $contact) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[VYORA] '.$this->contact->subject, replyTo: [$this->contact->email]);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.contact-received');
    }
}
