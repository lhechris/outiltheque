<?php

use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;

new class extends Component {

    use WithPagination;

    public string $search = '';
    public string $stateFilter = '';
    public string $paymentStateFilter = '';

    /**
     * Valeurs éditées en mémoire, indexées par reservation_id.
     * Ex: $edited[3] = ['date_start' => '2026-08-13', 'state' => 'confirmed', ...]
     */
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
        $user = auth()->user();

        return Reservation::query()
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
            ->where("user_id",$user->id)
            ->orderByDesc('date_start')
            ->paginate(15);
    }
};
?>

<div class="min-h-screen bg-gray-50">
    <main class="max-w-7xl mx-auto p-4 sm:p-6">

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Réservations</h2>
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
        </div>

        {{-- Tableau --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-4 py-3">Référence</th>
                        <th class="px-4 py-3">Outil</th>
                        <th class="px-4 py-3">Début</th>
                        <th class="px-4 py-3">Fin</th>
                        <th class="px-4 py-3">État</th>
                        <th class="px-4 py-3">Paiement</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($this->reservations as $reservation)
                        <tr
                            wire:key="reservation-{{ $reservation->id }}"
                        >
                            <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">
                               <a href="{{route('mesreservations.show',["reservation"=>$reservation->id])}}"> {{ $reservation->reference }} </a>
                            </td>

                            <td class="px-4 py-3 whitespace-nowrap">
                                <livewire:pastille color="{{$reservation->tool?->contract->color}}" class="border-0 py-1 pl-2.5 pr-7 text-xs font-medium">
                                    {{$reservation->tool?->name}}
                                </livewire:pastille>
                            </td>

                            <td class="px-4 py-3">
                                {{ Carbon::parse($reservation->date_start)->translatedFormat('l d F') }}
                            </td>

                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                                {{ Carbon::parse($reservation->date_end)->translatedFormat('l d F') }}
                            </td>

                            <td class="px-4 py-3">
                                {{Reservation::states()[$reservation->state]}}
                            </td>

                            <td class="px-4 py-3">
                                <livewire:pastille 
                                        color="{{ Reservation::getPaymentStateColor($reservation->payment_state) }}" 
                                        class="rounded-full border-0 py-1 pl-2.5 pr-7 text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                                {{ Reservation::payment_states()[$reservation->payment_state] }}
                                </livewire:pastille>                                
                            </td>
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
</div>