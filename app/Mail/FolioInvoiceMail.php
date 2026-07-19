<?php

namespace App\Mail;

use App\Models\Folio;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FolioInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Folio $folio) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice '.$this->folio->folio_no.' — '.$this->folio->property->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservations.invoice',
        );
    }
}
