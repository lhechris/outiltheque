<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

use App\Models\Reservation;

class NewResaForAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public ?int $amount;
    /**
     * Create a new message instance.
     */
    public function __construct(protected Reservation $resa,int $amount=null )
    {
        $this->amount = $amount;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('labobinette@machris.fr', 'Outiltheque de labo binette'),
            subject: 'Nouvelle Reservation '.$this->resa->tool->name,
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
                    'nom' => $this->resa->name,
                    'outil' => $this->resa->tool->name,
                    'debut' => \Carbon\Carbon::parse($this->resa->date_start)->translatedFormat('l d F'),
                    'fin' => \Carbon\Carbon::parse($this->resa->date_end)->translatedFormat('l d F'),
                    'prix' => $this->amount,
                    'paiement' =>$this->resa->payment_state,
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
