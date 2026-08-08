<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

use App\Models\Reservation;

class ConfirmResa extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(protected Reservation $resa)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('labobinette@machris.fr', 'Outiltheque de labo binette'),
            subject: 'Confirmation Reservation',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        if ($this->resa->isToPay()) {
            return new Content(
                view: 'emails.confirmtopay',
                with : [ 
                    'nom' => $this->resa->name,
                    'outil' => $this->resa->tool->name,
                    'debut' => $this->resa->date_start,
                    'fin' => $this->resa->date_end,
                    'prix' => $this->resa->tool->contract->price,
                    'reference' => $this->resa->reference
                ]
            );
        } else {

            return new Content(
                view: 'emails.confirm',
                with : [ 
                    'nom' => $this->resa->name,
                    'outil' => $this->resa->tool->name,
                    'debut' => $this->resa->date_start,
                    'fin' => $this->resa->date_end,
                    'reference' => $this->resa->reference
                ]
            );
        }
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
