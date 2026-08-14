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

    private ?int $amount;

    /**
     * Create a new message instance.
     */
    public function __construct(protected Reservation $resa, int $amount=null)
    {
        $this->amount = $amount;
        \Log::debug("contructeur");
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        \Log::debug("envelope");
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
        \Log::debug("cnotent");
        if ($this->amount) {
            return new Content(
                view: 'emails.confirmtopay',
                with : [ 
                    'nom' => $this->resa->name,
                    'outil' => $this->resa->tool->name,
                    'debut' => $this->resa->date_start,
                    'fin' => $this->resa->date_end,
                    'prix' => $this->amount,
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
