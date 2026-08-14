<?php

use App\Models\Contract;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {

    use WithPagination;

    public string $search = '';
    public string $contractFilter = '';
    public string $paymentStateFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingContractFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPaymentStateFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Liste des contrats, pour le filtre.
     */
    #[Computed]
    public function contractsList()
    {
        return Contract::query()->orderBy('name')->get();
    }

    /**
     * Une ligne = un couple (utilisateur, contrat), c'est-à-dire une ligne
     * de la table pivot contract_user, avec son état de paiement.
     */
    #[Computed]
    public function rows()
    {
        $user = auth()->user();

        return DB::table('contract_user')
            ->join('users', 'users.id', '=', 'contract_user.user_id')
            ->join('contracts', 'contracts.id', '=', 'contract_user.contract_id')
            ->select([
                'contract_user.user_id',
                'contract_user.contract_id',
                'contract_user.payment_state',
                'users.name as user_name',
                'users.firstname as user_firstname',
                'users.email as user_email',
                'contracts.name as contract_name',
                'contracts.unit as contract_unit',
                'contracts.flat_rate as contract_flat_rate',
                'contracts.color as contract_color',
                'contracts.restriction as contract_restriction',
            ])
            ->where("user_id",$user->id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('users.name', 'like', "%{$this->search}%")
                        ->orWhere('users.firstname', 'like', "%{$this->search}%")
                        ->orWhere('users.email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->contractFilter, fn ($query) => $query->where('contract_user.contract_id', $this->contractFilter))
            ->when($this->paymentStateFilter, fn ($query) => $query->where('contract_user.payment_state', $this->paymentStateFilter))
            ->orderBy('users.name')
            ->orderBy('users.firstname')
            ->orderBy('contracts.name')
            ->paginate(15);
    }
};
?>

<div class="min-h-screen bg-gray-50">
    <main class="max-w-7xl mx-auto p-4 sm:p-6">

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Contrats des utilisateurs</h2>
            <span class="text-sm text-gray-500">{{ $this->rows->total() }} contrat(s) attribué(s)</span>
        </div>

        {{-- Filtres --}}
        <div class="flex flex-col sm:flex-row gap-2 mb-4">
            <input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="Nom, prénom, email..."
                class="flex-1 rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
            >

            <select wire:model.live="contractFilter" class="rounded-lg border-gray-300 text-sm">
                <option value="">Tous les contrats</option>
                @foreach ($this->contractsList as $contract)
                    <option value="{{ $contract->id }}">{{ $contract->name }}</option>
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
                        <th class="px-4 py-3">Utilisateur</th>
                        <th class="px-4 py-3">Contrat</th>
                        <th class="px-4 py-3">Tarif</th>
                        <th class="px-4 py-3">Restriction</th>
                        <th class="px-4 py-3">Paiement</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($this->rows as $row)
                        <tr wire:key="contract-user-{{ $row->user_id }}-{{ $row->contract_id }}">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">
                                    {{ $row->user_firstname }} {{ $row->user_name }}
                                </div>
                                <div class="text-xs text-gray-500">{{ $row->user_email }}</div>
                            </td>

                            <td class="px-4 py-3 whitespace-nowrap">
                                <livewire:pastille color="{{$row->contract_color}}" class="border-0 py-1 pl-2.5 pr-7 text-xs font-medium">
                                    {{$row->contract_name}}
                                </livewire:passtille>
                            </td>

                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                @if (! is_null($row->contract_flat_rate))
                                    {{ number_format((float) $row->contract_flat_rate, 2, ',', ' ') }} €
                                    @if ($row->contract_unit)
                                        <span class="text-gray-400">/ {{ $row->contract_unit }}</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>

                            <td class="px-4 py-3 text-gray-500">
                                {{ $row->contract_restriction ?: '—' }}
                            </td>

                            <td class="px-4 py-3 ">
                                <livewire:pastille 
                                        color="{{ Reservation::GetPaymentStateColor($row->payment_state) }}" 
                                        class="rounded-full border-0 py-1 pl-2.5 pr-7 text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                                {{ Reservation::payment_states()[$row->payment_state] }}
                                </livewire:pastille>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                                Aucun contrat attribué trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $this->rows->links() }}
        </div>
    </main>
</div>