<?php

use Livewire\Component;
use App\Models\Reservation;
use App\Services\SrvReservation;

new class extends Component
{
    public Reservation $reservation;

    public function mount($ref)
    {
        $this->reservation = Reservation::where("reference","$ref")->firstOrFail();
    }

    public function recommencer() {

        $srvResa = app(SrvReservation::class);
        if ($this->reservation && $srvResa->restartPay($this->reservation)) {
            redirect(route('payments.select',['ref'=> $this->reservation->reference]));
            
        } else {
            \Log::info($this->reservation->reference." Impossible de recommencer la resa, mauvais etat [".$this->reservation->state."] attendu [Réservé] ou [Paiement]");
        }

    }

    public function annuler() {

        if (($this->reservation) && 
                (($this->reservation->isReserved()) || ($this->reservation->isPayment())))
        {                
                $this->reservation->setCancelled();
            
        } else {
            \Log::info($this->reservation->reference." Impossible d'annuler la resa, mauvais etat [".$this->reservation->state."] attendu [Réservé] ou [Paiement]");
        }


    }

};
?>

<div class="grid justify-center gap-5 py-4" >
    <h1 >Erreur encaissement</h1>        
    <div>
        <p>Code réservation : {{ $reservation->reference }}</p>
        <p>Date d'emprunt : {{ $reservation->date_start }}</p>
        <button class="bg-blue-500 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded" 
                    id="confirm" 
                    type="button"
                    wire:click="recommencer"
            >Recommencer</button>
            <button class="bg-blue-500 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded" 
                    id="confirm" 
                    type="button"
                    wire:click="annuler"
            >Annuler</button>
    </div>
</div>
