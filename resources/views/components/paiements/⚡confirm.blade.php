<?php

use Livewire\Component;
use App\Models\Reservation;
use App\Mail\ConfirmResa;
use App\Services\Helloasso\Payment;

new class extends Component
{
    public Reservation $reservation;
    public bool $isConfirmed=false;
    public bool $isUnknown=false;

    public function mount($ref) {

        $this->reservation = Reservation::where("reference",$ref)->firstOrFail();

        if ($this->reservation->isConfirmed()) {
            $this->isConfirmed = true;
        
        } else if ($this->reservation->isPendingHA()) {

            if (Payment::check($this->reservation)) {

                if ($this->reservation->isPayment()) {
                    
                    $this->reservation->setPaymentHA();
                    //Envoi du mail
                    Mail::to($this->reservation->email)->send(new ConfirmResa($this->reservation));
                }
                $this->isConfirmed=true;

            } else {
                $this->isConfirmed=false;
            }                             
        } else {
            $isUnknown = true;
        }

             
    }

    public function retour() {
        return redirect(route('tools.index'));
    }

    public function annuler() {
        $this->reservation->setCancelled();
        return $this->retour();        
    }

    public function recommencer() {
        Payment::init($this->reservation);
     }
};
?>

<div>
    @if ($isConfirmed)
    <div class="bg-green-100 text-green-800 p-4 rounded">
        Le paiement helloasso est confirmé
    </div>
    <button class="bg-blue-500 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded" 
            id="confirm" 
            type="button"
            wire:click="retour"
    >Retour</button>
    @elseif ($isUnknown)
    <div class="bg-red-100 text-red-800 p-4 rounded">
        Vous n'avez rien à faire là !
    </div>
    <button class="bg-blue-500 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded" 
            id="confirm" 
            type="button"
            wire:click="retour"
    >Retour</button>

    @else
    <div class="bg-red-100 text-red-800 p-4 rounded">
        Le paiement n'a pas été confirmé
    </div>
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

    @endif
</div>