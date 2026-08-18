<?php

use Livewire\Component;
use App\Models\Reservation;
use App\Models\Parameter;
use Illuminate\Support\Facades\Mail;

use App\Services\SrvReservation;
use App\Services\SrvPayment;
use App\Services\Helloasso\Payment;

new class extends Component
{
    public Reservation $reservation;
    public bool $needToPay;

    public function mount($ref)
    {
        if (!auth()->check()) { return (redirect(route('login')));}

        $this->reservation = Reservation::where("reference","$ref")->firstOrFail();

        //L'utilisateur doi être celui de la résa
        if ($this->reservation->user_id != auth()->user()->id) {
            \Log::info("L'utilisateur ".auth()->user()->id." a tenté la page paiement de ".$ref);
            return (redirect(route('tools.index')));
        }        
        
        $srvResa = app(SrvReservation::class);

        $this->needToPay = $srvResa->needToPay($this->reservation);
    }

    public function handleCash() {
        $srvPay = app(SrvPayment::class);
        if ($srvPay->pay_by_cash($this->reservation)) {
            Flux::toast(
                text: $srvPay->getMessage(),
                variant: 'success',
            );
            $this->needToPay = false;

        } else {
            Flux::toast(
                text: $srvPay->getMessage(),
                variant: 'warning',
            );
        }
    }

    public function handleHA() {
        $srvPay = app(SrvPayment::class);
        if ($srvPay->pay_by_ha($this->reservation)) {
            Flux::toast(
                text: $srvPay->getMessage(),
                variant: 'success',
            );
            $this->needToPay = false;

        } else {
            Flux::toast(
                text: $srvPay->getMessage(),
                variant: 'warning',
            );
        }        
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
    @if($needToPay)

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

            <button type="button"
                    class="w-full bg-red-500 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg text-sm transition-colors"
                    wire:click="retour"
            >
                Retour
            </button>
        </div>

    @endif
</div>