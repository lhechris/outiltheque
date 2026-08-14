<?php

use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Livewire\Component;

new class extends Component {

    // Route::livewire('/reservations/{reservation}', 'reservations.show');
    public Reservation $reservation;

    public string $date_start = '';
    public string $state = '';
    public string $payment_state = '';
    public ?string $comment = null;

    public function mount(): void
    {
        $this->reservation->loadMissing('tool');

        $this->date_start   = Carbon::parse($this->reservation->date_start)->format('Y-m-d');
        $this->state        = $this->reservation->state;
        $this->payment_state = $this->reservation->payment_state;
        $this->comment       = $this->reservation->comment;
    }

    public function save(): void
    {
        $newStart = Carbon::parse($this->date_start);
        // Créneau fixe jeudi -> mercredi (6 jours après le début)
        $newEnd = $newStart->copy()->addDays(6);

        if (! $newStart->equalTo($this->reservation->date_start)) {
            $overlapping = Reservation::where('tool_id', $this->reservation->tool_id)
                ->where('id', '!=', $this->reservation->id)
                ->where('state', '!=', 'cancelled')
                ->where('date_start', '<', $newEnd)
                ->where('date_end', '>', $newStart)
                ->lockForUpdate()
                ->count();

            if ($overlapping >= $this->reservation->tool->number) {
                $this->addError(
                    'date_start',
                    "Plus de disponibilité pour {$this->reservation->tool->name} sur cette période."
                );
                return;
            }
        }

        $this->reservation->update([
            'date_start'    => $newStart,
            'date_end'      => $newEnd,
            'state'         => $this->state,
            'payment_state' => $this->payment_state,
            'comment'       => $this->comment,
        ]);

        $this->reservation->refresh();

        Flux::toast(
            text: "Réservation {$this->reservation->reference} mise à jour.",
            variant: 'success',
        );
    }

    public function cancel(): void
    {
        $this->reservation->update(['state' => 'cancelled']);
        $this->state = 'cancelled';

        Flux::toast(
            text: "Réservation {$this->reservation->reference} annulée.",
            variant: 'warning',
        );
    }
};
?>

<div class="min-h-screen bg-gray-50">
    <main class="max-w-3xl mx-auto p-4 sm:p-6">

        <div class="flex items-center justify-between mb-4">
            <a href="{{ route('reservations.index') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
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

            {{-- Champs éditables --}}
            <div class="p-5 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                        Date de début
                    </label>
                    <input
                        type="date"
                        wire:model="date_start"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    @error('date_start')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-1">
                        Fin prévue : {{ Carbon::parse($date_start)->addDays(6)->translatedFormat('d/m/Y') }}
                    </p>
                </div>

                <div></div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                        État
                    </label>
                    <select wire:model="state" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach (Reservation::states() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                        Paiement
                    </label>
                    <select wire:model="payment_state" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach (Reservation::payment_states() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
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
                    @if ($reservation->state === 'cancelled') disabled @endif
                >
                    Annuler la réservation
                </button>

                <button
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="px-4 py-1.5 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="save">Enregistrer</span>
                    <span wire:loading wire:target="save">Enregistrement...</span>
                </button>
            </div>
        </div>
    </main>
</div>