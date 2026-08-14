<?php

use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Livewire\Component;
use App\Services\SrvReservation;
new class extends Component {

    // Route::livewire('/reservations/{reservation}', 'reservations.show');
    public Reservation $reservation;

    public string $date_start = '';
    public string $date_end = '';
    public string $state = '';
    public string $payment_state = '';
    public ?string $comment = null;

    public function mount(): void
    {
        $this->reservation->loadMissing('tool');

        $this->date_start   = Carbon::parse($this->reservation->date_start)->format('Y-m-d');
        $this->date_end     = Carbon::parse($this->reservation->date_end)->format('Y-m-d');
        $this->state        = $this->reservation->state;
        $this->payment_state = $this->reservation->payment_state;
        $this->comment       = $this->reservation->comment;
    }

    public function cancel(): void
    {
        $srv = new SrvReservation();
        if ($srv->cancel($this->reservation)) {
            $this->state = 'cancelled';

            Flux::toast(
                text: $srv->getMessage(),
                variant: 'success',
            );
        } else {
            Flux::toast(
                text: $srv->getMessage(),
                variant: 'warning',
            );

        }
    }
};
?>

<div class="min-h-screen bg-gray-50">
    <main class="max-w-3xl mx-auto p-4 sm:p-6">

        <div class="flex items-center justify-between mb-4">
            <a href="{{ route('mesreservations.index') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Retour aux réservations
            </a>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

            {{-- En-tête --}}
            <div class="flex items-start justify-between p-5 border-b border-gray-100">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $reservation->reference }}</h2>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $reservation->tool?->name }}</p>
                </div>

                @if ($reservation->tool?->icon)
                    <img src="{{ Storage::url($reservation->tool->icon) }}" alt="{{ $reservation->tool->name }}" class="h-14 w-14 object-cover rounded-lg">
                @endif
            </div>

            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-6">

                {{-- Informations client (lecture seule) --}}
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Client</h3>
                    <dl class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Nom</dt>
                            <dd class="text-gray-900 font-medium">{{ $reservation->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Email</dt>
                            <dd class="text-gray-900">{{ $reservation->email }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Téléphone</dt>
                            <dd class="text-gray-900">{{ $reservation->phone }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Informations outil (lecture seule) --}}
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Outil</h3>
                    <dl class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Catégorie</dt>
                            <dd class="text-gray-900">{{ $reservation->tool?->category?->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Tarif unité</dt>
                            <dd class="text-gray-900">
                                @if ($reservation->tool?->contract)
                                    {{ number_format($reservation->tool->contract->unit, 2, ',', ' ') }} €
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Tarif forfait</dt>
                            <dd class="text-gray-900">
                                @if ($reservation->tool?->contract)
                                    {{ number_format($reservation->tool->contract->flat_rate, 2, ',', ' ') }} €
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Stock</dt>
                            <dd class="text-gray-900">{{ $reservation->tool?->number }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="p-5 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div>
                    <p class="text-xs text-gray-400 mt-1">
                        Date d'emprunt : {{ Carbon::parse($date_start)->translatedFormat('l d F') }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        Fin prévue : {{ Carbon::parse($date_end)->translatedFormat('l d F') }}
                    </p>
                </div>

                <div></div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                        État
                    </label>
                    <p>{{Reservation::states()[$reservation->state]}}</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                        Paiement
                    </label>
                    <p>{{Reservation::payment_states()[$reservation->payment_state]}}</p>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                        Commentaire
                    </label>
                    <textarea
                        wire:model="comment"
                        rows="3"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    ></textarea>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between p-5 border-t border-gray-100 bg-gray-50">
                <button
                    wire:click="cancel"
                    wire:confirm="Confirmer l'annulation de cette réservation ?"
                    class="px-3 py-1.5 rounded-lg border border-red-200 text-red-600 text-sm font-medium hover:bg-red-50"
                    @if (!$reservation->isReserved()) hidden @endif
                >
                    Annuler la réservation
                </button>
            </div>
        </div>
    </main>
</div>