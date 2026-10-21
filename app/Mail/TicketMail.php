<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private Ticket $ticket)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu boleto para '.$this->ticket->event->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.ticket',
            with: [
                'ticket' => $this->ticket,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn () => $this->ticket->qrCode(),
                'boleto-'.$this->ticket->code.'.svg'
            )->withMime('image/svg+xml'),
        ];
    }
}
