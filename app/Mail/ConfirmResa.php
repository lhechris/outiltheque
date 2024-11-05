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


    private function dateToFrench($date, $format) 
    {
        $english_days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
        $french_days = array('lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche');
        $english_months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
        $french_months = array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre');
        
        $d= date_create($date);
        $f = date_format($d,$format);
        return str_replace($english_months, $french_months, str_replace($english_days, $french_days, $f ) );
    }
    

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $date1 = $this->dateToFrench($this->resa->debut,'l j F Y');
        $date2 = $this->dateToFrench($this->resa->fin,'l j F Y');        

        if ($this->resa->paiement_state== "A payer") {
            return new Content(
                view: 'emails.confirmtopay',
                with : [ 
                    'nom' => $this->resa->nom,
                    'outil' => $this->resa->nomoutil,
                    'debut' => $date1,
                    'fin' => $date2,
                    'prix' => $this->resa->prix,
                ]
            );
        } else {

            return new Content(
                view: 'emails.confirm',
                with : [ 
                    'nom' => $this->resa->nom,
                    'outil' => $this->resa->nomoutil,
                    'debut' => $date1,
                    'fin' => $date2
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
