<?php

use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {

    use WithPagination;    

    public string $search = '';
    public string $stateFilter = '';
    public string $paymentStateFilter = '';
    public bool $historique = false;
    public bool $mine = false;

    public bool $editable = true;


    public function mount() {
        $this->editable = !($this->historique || $this->mine);
    }

    public array $edited = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStateFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPaymentStateFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function reservations()
    {
        $query = Reservation::query()
                ->with('tool')
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('reference', 'like', "%{$this->search}%")
                            ->orWhere('name', 'like', "%{$this->search}%")
                            ->orWhere('email', 'like', "%{$this->search}%");
                    });
                })
                ->when($this->stateFilter, fn ($query) => $query->where('state', $this->stateFilter))
                ->when($this->paymentStateFilter, fn ($query) => $query->where('payment_state', $this->paymentStateFilter))
                ->orderBy('date_start','desc');
        
        if ($this->historique) {
            $query->where("date_end",'<', Carbon::now());
        } else {
            $query->where("date_end",'>=', Carbon::now());
        }

        if ($this->mine) {
            $query->where("user_id",auth()->user()->id);
        }

        return $query->paginate(15);
    }

    /**
     * Initialise le tableau $edited pour une réservation donnée
     * la première fois qu'on touche à ses champs (lazy init côté vue).
     */
    public function ensureEdited(int $id, string $dateStart, string $state, string $paymentState): void
    {
        if (! isset($this->edited[$id])) {
            $this->edited[$id] = [
                'date_start'    => $dateStart,
                'state'         => $state,
                'payment_state' => $paymentState,
            ];
        }
    }

    public function save(int $id): void
    {
        if (!$this->editable) {
            \Log::info(auth()->users()->email." Veut modifier les résa mais c'est interdit");
            Flux::toast(
                text: "Impossible de modifier l'historique",
                variant: 'alert',
            );            
            return;
        }

        $reservation = Reservation::with('tool')->findOrFail($id);
        $data = $this->edited[$id] ?? null;

        if (! $data) {
            return;
        }

        $newStart = Carbon::parse($data['date_start']);
        // Créneau fixe jeudi -> mercredi (6 jours après le début)
        $newEnd = $newStart->copy()->addDays(6);

        // Vérification de non-dépassement du stock disponible sur la nouvelle période,
        // en excluant la réservation courante et les réservations annulées.
        if (! $newStart->equalTo($reservation->date_start)) {
            $overlapping = Reservation::where('tool_id', $reservation->tool_id)
                ->where('id', '!=', $reservation->id)
                ->where('state', '!=', 'cancelled')
                ->where('date_start', '<', $newEnd)
                ->where('date_end', '>', $newStart)
                ->lockForUpdate()
                ->count();

            if ($overlapping >= $reservation->tool->number) {
                $this->addError(
                    "edited.{$id}.date_start",
                    "Plus de disponibilité pour {$reservation->tool->name} sur cette période."
                );
                return;
            }
        }

        $reservation->update([
            'date_start'    => $newStart,
            'date_end'      => $newEnd,
            'state'         => $data['state'],
            'payment_state' => $data['payment_state'],
        ]);

        unset($this->edited[$id]);
        unset($this->reservations);

        $this->dispatch('reservation-saved');

        Flux::toast(
            text: "Réservation {$reservation->reference} mise à jour.",
            variant: 'success',
        );
    }
};
?>

<main class="max-w-7xl mx-auto p-4 sm:p-6">

    <div class="flex items-center justify-between mb-4">
        @if ($this->historique)
        <h2 class="text-lg font-semibold text-gray-900">Historique des réservations</h2>
        @else
        <h2 class="text-lg font-semibold text-gray-900">Réservations en cours</h2>
        @endif
        <span class="text-sm text-gray-500">{{ $this->reservations->total() }} réservation(s)</span>
    </div>

    {{-- Filtres --}}
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <input
            type="text"
            wire:model.live.debounce.400ms="search"
            placeholder="Référence, nom, email..."
            class="flex-1 rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
        >

        <select wire:model.live="stateFilter" class="rounded-lg border-gray-300 text-sm">
            <option value="">Tous les états</option>
            @foreach (Reservation::states() as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>

        <select wire:model.live="paymentStateFilter" class="rounded-lg border-gray-300 text-sm">
            <option value="">Tous les paiements</option>
            @foreach (Reservation::payment_states() as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    {{-- Tableau --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <th class="px-4 py-3">Référence</th>
                    <th class="px-4 py-3">Outil</th>
                    @if(!$this->mine)
                    <th class="px-4 py-3">Client</th>
                    @endif
                    <th class="px-4 py-3">Début</th>
                    <th class="px-4 py-3">Fin</th>
                    <th class="px-4 py-3">État</th>
                    <th class="px-4 py-3">Paiement</th>
                    @if($this->editable)
                    <th class="px-4 py-3"></th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($this->reservations as $reservation)
                    <tr
                        wire:key="reservation-{{ $reservation->id }}"
                        x-init="$wire.ensureEdited(
                            {{ $reservation->id }},
                            '{{ Carbon::parse($reservation->date_start)->format('Y-m-d') }}',
                            '{{ $reservation->state }}',
                            '{{ $reservation->payment_state }}',
                        )"
                    >
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">
                            @if($this->editable)
                            <a href="{{route('reservations.show',["reservation"=>$reservation->id])}}"> {{ $reservation->reference }} </a>
                            @else
                             {{ $reservation->reference }}
                            @endif
                        </td>

                        <td class="px-4 py-3 whitespace-nowrap">
                            <livewire:pastille color="{{$reservation->tool->contract->color}}" class="border-0 py-1 pl-2.5 pr-7 text-xs font-medium">
                            {{ $reservation->tool?->name }}
                            </livewire:pastille>
                        </td>
                        @if(!$this->mine)
                        <td class="px-4 py-3">
                            <div class="text-gray-900">{{ $reservation->name }}</div>
                            <div class="text-xs text-gray-500">{{ $reservation->email }}</div>
                        </td>
                        @endif

                        <td class="px-4 py-3">
                            @if($this->editable)
                            <input
                                type="date"
                                wire:model="edited.{{ $reservation->id }}.date_start"
                                class="rounded-lg border-gray-300 text-sm w-36"
                            >
                            @error("edited.{$reservation->id}.date_start")
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                            @else
                            {{ Carbon::parse($reservation->date_start)->translatedFormat('d/m/Y')  }}
                            @endif
                        </td>

                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                            {{ Carbon::parse($reservation->date_end)->translatedFormat('d/m/Y') }}
                        </td>

                        <td class="px-4 py-3">
                            @if ($this->editable)
                            <select
                                wire:model="edited.{{ $reservation->id }}.state"
                                class="rounded-lg border-gray-300 text-sm"
                            >
                                @foreach (Reservation::states() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @else
                            {{ $reservation->state }}
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            @if ($this->editable)
                            <select
                                wire:model="edited.{{ $reservation->id }}.payment_state"
                                class="rounded-lg border-gray-300 text-sm"
                            >
                                @foreach (Reservation::payment_states() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @else
                            <livewire:pastille 
                                    color="{{ Reservation::getPaymentStateColor($reservation->payment_state) }}" 
                                    class="rounded-full border-0 py-1 pl-2.5 pr-7 text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                            {{ Reservation::payment_states()[$reservation->payment_state] }}
                            </livewire:pastille>                                
                            @endif
                        </td>

                        @if($this->editable)
                        <td class="px-4 py-3 text-right">
                            <button
                                wire:click="save({{ $reservation->id }})"
                                wire:loading.attr="disabled"
                                wire:target="save({{ $reservation->id }})"
                                class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700 disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="save({{ $reservation->id }})">Enregistrer</span>
                                <span wire:loading wire:target="save({{ $reservation->id }})">...</span>
                            </button>
                        </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-gray-500">
                            Aucune réservation trouvée.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $this->reservations->links() }}
    </div>
</main>
