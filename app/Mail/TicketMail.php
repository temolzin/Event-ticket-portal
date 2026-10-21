<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Symfony\Component\Mime\Part\DataPart;

class TicketMail extends Mailable
{
    use Queueable, SerializesModels;

    public const QR_CID = 'qr@ticket-portal';

    public function __construct(private Ticket $ticket)
    {
        $this->withSymfonyMessage(function (SymfonyEmail $message) {
            $message->addPart(
                (new DataPart($this->ticket->qrPng(), 'qr.png', 'image/png'))
                    ->asInline()
                    ->setContentId(self::QR_CID)
            );
        });
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
                fn () => $this->ticket->qrPng(),
                'boleto-'.$this->ticket->code.'.png'
            )->withMime('image/png'),
        ];
    }
}
