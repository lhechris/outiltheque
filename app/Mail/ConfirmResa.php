<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

use App\Models\Reservations;
use App\Models\Outils;

class ConfirmResa extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        protected Reservations $resa
         )
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

        if ($this->resa->paiement_state== "A payer") {
            return new Content(
                view: 'emails.confirmtopay',
                with : [ 
                    'nom' => $this->resa->nom,
                    'outil' => $this->resa->nomoutil,
                    'debut' => $this->resa->debutfrench(),
                    'fin' => $this->resa->finfrench(),
                    'prix' => $this->resa->prix,
                    'reference' => $this->resa->reference
                ]
            );
        } else {

            return new Content(
                view: 'emails.confirm',
                with : [ 
                    'nom' => $this->resa->nom,
                    'outil' => $this->resa->nomoutil,
                    'debut' => $this->resa->debutfrench(),
                    'fin' => $this->resa->finfrench(),
                    'reference' => $this->resa->reference
                ]
            );
        }
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
