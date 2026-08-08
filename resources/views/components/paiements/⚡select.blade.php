<?php

use Livewire\Component;
use App\Models\Reservation;
use App\Models\Parameter;
use App\Services\Helloasso\Payment;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConfirmResa;
use App\Mail\NewResaForAdmin;

new class extends Component
{
    public Reservation $reservation;

    public function mount($ref)
    {
        $this->reservation = Reservation::where("reference","$ref")->firstOrFail();
    }

    public function handleCash() {
        if (($this->reservation) && ($this->reservation->isReserved())) {

            \Log::info("Paiement cash");
            \Log::info("On envoi le mail a ".$this->reservation->email);
            $this->reservation->setPaymentCash();
            Mail::to($this->reservation->email)->send(new ConfirmResa($this->reservation));
            Mail::to(env('MAIL_RESPONSABLE_RESA',''))->send(new NewResaForAdmin($this->reservation));            

            session()->flash('message','Paiement cash sélectionné un email vous à été envoyé');

        } else {
            \Log::info("Paiement cash erreur : soit pas de réservation, soit l'état n'est pas reservé");
        }

    }

    public function handleHA() {
        Payment::init($this->reservation);
    }

    public function handleCancel() {
        if ($this->reservation) {
            $this->reservation->setCancelled();
            return $this->retour();
        }
    }
    public function retour() {
            return redirect(route('tools.index'));
    }
};
?>

<div class="flex flex-col">
    @if(($reservation) && ($reservation->isReserved()))

        <div class="max-w-md mx-auto w-full py-6">
            <p class="text-center text-gray-700 mb-6">
                Page Paiement de la réservation <span class="font-semibold">{{ $reservation->reference }}</span>
            </p>

            <div class="flex flex-col gap-3">
                <button type="button"
                        class="w-full flex items-center justify-center rounded-lg text-center text-sm font-medium text-white shadow-sm transition-all hover:opacity-90 focus:ring focus:ring-blue-200 disabled:cursor-not-allowed disabled:border-blue-300 disabled:bg-blue-300"
                        wire:click="handleHA"
                >
                    <img src="{{ asset('images/payer-avec-helloasso.svg') }}" class="h-full w-auto max-w-full object-contain" />
                </button>

                <button type="button"
                        class="w-full h-12 bg-blue-500 hover:bg-blue-700 text-white font-bold rounded-lg text-sm transition-colors"
                        wire:click="handleCash"
                >
                    Payer plus tard
                </button>

                <button type="button"
                        class="w-full h-12 bg-red-500 hover:bg-red-700 text-white font-bold rounded-lg text-sm transition-colors"
                        wire:click="handleCancel"
                >
                    Annuler la réservation
                </button>
            </div>
        </div>

    @else

        <div class="max-w-md mx-auto w-full py-6">

            @if ($reservation && $reservation->isConfirmed())
                <div class="grid justify-center gap-3 py-4 text-center">
                    <h1 class="text-lg font-semibold text-gray-900">Confirmation de votre réservation</h1>
                    <div class="text-sm text-gray-700">
                        <p>Code réservation : <span class="font-medium">{{ $reservation->reference }}</span></p>
                        <p>Date d'emprunt : {{ \Carbon\Carbon::parse($reservation->date_start)->translatedFormat('l d F Y') }}</p>
                    </div>
                </div>
            @endif

            @if (session()->has('message'))
                <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-4">
                    {{ session('message') }}
                </div>
            @else
                <div class="bg-red-100 text-red-800 p-4 rounded-lg mb-4">
                    Moyen de paiement déjà validé
                </div>
            @endif

            <button type="button"
                    class="w-full bg-red-500 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg text-sm transition-colors"
                    wire:click="retour"
            >
                Retour
            </button>
        </div>

    @endif
</div>