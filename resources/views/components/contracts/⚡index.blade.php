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

    /**
     * Classes Tailwind pour le badge d'état de paiement, basées sur les
     * constantes Reservation::PAYMENT_STATE_* (réutilisées pour le pivot
     * contract_user).
     */
    public function paymentBadgeClass(?string $state): string
    {
        return match ($state) {
            Reservation::PAYMENT_STATE_UNPAID => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200',
            Reservation::PAYMENT_STATE_TO_PAY => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200',
            Reservation::PAYMENT_STATE_HA_PENDING => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200',
            Reservation::PAYMENT_STATE_HA_PAYED, Reservation::PAYMENT_STATE_PAYED => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200',
            Reservation::PAYMENT_STATE_FORFAIT => 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200',
            default => 'bg-gray-100 text-gray-700 ring-1 ring-inset ring-gray-200',
        };
    }
    /**
     * Met à jour l'état de paiement d'un couple (utilisateur, contrat)
     * directement dans la table pivot contract_user. Sauvegarde immédiate
     * au changement de sélection (pas de bouton "Enregistrer" séparé).
     */
    public function updatePaymentState(int $userId, int $contractId, string $paymentState): void
    {
        if (! array_key_exists($paymentState, Reservation::payment_states())) {
            $this->addError('paymentState', "État de paiement invalide.");
            return;
        }

        DB::table('contract_user')
            ->where('user_id', $userId)
            ->where('contract_id', $contractId)
            ->update(['payment_state' => $paymentState]);

        unset($this->rows);

        Flux::toast(
            text: 'État de paiement mis à jour.',
            variant: 'success',
        );
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
                                    {{ $row->contract_name }}
                                </livewire:pastille>
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

                            <td class="px-4 py-3">
                                <select
                                    wire:change="updatePaymentState({{ $row->user_id }}, {{ $row->contract_id }}, $event.target.value)"
                                    wire:loading.attr="disabled"
                                    wire:target="updatePaymentState({{ $row->user_id }}, {{ $row->contract_id }})"
                                    class="rounded-full border-0 py-1 pl-2.5 pr-7 text-xs font-medium focus:ring-2 focus:ring-indigo-500 {{ $this->paymentBadgeClass($row->payment_state) }}"
                                >
                                    @if (empty($row->payment_state) || ! array_key_exists($row->payment_state, Reservation::payment_states()))
                                        <option value="" selected disabled>Non défini</option>
                                    @endif
                                    @foreach (Reservation::payment_states() as $value => $label)
                                        <option value="{{ $value }}" @selected($row->payment_state === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
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