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

class NewResaForAdmin extends Mailable
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
            subject: 'Nouvelle Reservation '.$this->resa->nomoutil,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
            return new Content(
                view: 'emails.new_resa_admin',
                with : [ 
                    'nom' => $this->resa->nom,
                    'outil' => $this->resa->nomoutil,
                    'debut' => $this->resa->debutfrench(),
                    'fin' => $this->resa->finfrench(),
                    'prix' => $this->resa->prix,
                    'paiement' =>$this->resa->paiement_state,
                    'reference' => $this->resa->reference
                ]
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
