<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

use App\Models\Reservation;
use App\Mail\ConfirmResa;
use App\Mail\NewResaForAdmin;
use App\Services\Helloasso\Payment;
use Carbon\Carbon;

class SrvPayment
{
    private string $message = "";

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Active le paiement en cash.
     * Envoi un mail de confirmation au demandeur et aux admins.
     */
    public function pay_by_cash(Reservation $reservation): bool
    {
        return $this->pay($reservation, 'cash');
    }

    /**
     * Active le paiement HelloAsso.
     * Envoi un mail de confirmation au demandeur et aux admins.
     */
    public function pay_by_ha(Reservation $reservation): bool
    {
        return $this->pay($reservation, 'ha');
    }

    /**
     * Logique commune aux deux modes de paiement (cash / helloasso) :
     * - vérifie que la réservation est bien à l'état "reservé"
     * - calcule le montant (unité ou forfait)
     * - met à jour le payment_state de la réservation, ou celui du pivot
     *   contrat dans le cas d'un forfait
     * - initialise le paiement HelloAsso si besoin
     * - confirme la réservation et envoi les emails
     */
    private function pay(Reservation $reservation, string $mode): bool
    {
        if (!$reservation->isReserved()) {
            $this->message = 'Impossible de payer cette réservation';
            \Log::info("Paiement {$mode} erreur : soit pas de réservation, soit l'état n'est pas reservé");
            return false;
        }

        \Log::info("{$reservation->reference} Paiement " . ($mode === 'cash' ? 'cash' : 'HelloAsso'));

        $amount = $reservation->tool->contract->unit;
        $targetState = $mode === 'cash'
            ? Reservation::PAYMENT_STATE_TO_PAY
            : Reservation::PAYMENT_STATE_HA_PENDING;

        if ($reservation->payment_state == Reservation::PAYMENT_STATE_UNPAID) {
            // Paiement à l'unité
            $reservation->payment_state = $targetState;

        } elseif ($reservation->payment_state == Reservation::PAYMENT_STATE_FORFAIT) {
            // Paiement au forfait : le montant et l'état à mettre à jour
            // sont ceux du pivot contrat, pas de la réservation.
            $amount = $reservation->tool->contract->flat_rate;

            if (!$this->updateContractPivot($reservation, $targetState)) {
                return false;
            }

        } else {
            // Etat incohérent : ne devrait jamais arriver
            \Log::info("Problème avec la résa {$reservation->reference} les états sont incohérents {$reservation->state} {$reservation->payment_state}");
            $this->message = "Problème d'état avec la réservation, veuillez consulter les administrateurs.";
            return false;
        }

        if ($mode === 'ha') {
            Payment::init($reservation, $amount);
        }

        // La réservation est confirmée
        $reservation->state = Reservation::STATE_CONFIRMED;
        $reservation->update();

        $this->sendEmails($reservation, $amount);

        $this->message = $mode === 'cash'
            ? 'Paiement cash sélectionné un email vous à été envoyé'
            : 'Paiement HelloAsso sélectionné un email vous à été envoyé';

        return true;
    }

    /**
     * Met à jour (ou crée) le pivot contrat de l'utilisateur avec le nouvel
     * état de paiement, en vérifiant que l'état actuel est bien cohérent
     * (UNPAID). Si le pivot n'existe pas encore, il est créé (attach).
     */
    private function updateContractPivot(Reservation $reservation, string $newPivotState): bool
    {
        $contractId = $reservation->tool->contract_id;

        $usrContract = $reservation->user->contracts()
            ->where('contracts.id', $contractId)
            ->first();

        if (!$usrContract) {
            $expiration = now()->month <= 8
                                ? Carbon::create(now()->year, 8, 31)->endOfDay()
                                : Carbon::create(now()->year + 1, 8, 31)->endOfDay();

            $reservation->user->contracts()->attach($contractId, [
                'payment_state' => $newPivotState,
                'begin' => now(),
                'expire' => $expiration
            ]);
            return true;
        }

        if ($usrContract->pivot->payment_state != Reservation::PAYMENT_STATE_UNPAID) {
            // Ce n'est pas normal d'être ici
            \Log::info("Problème avec la résa {$reservation->reference} les états sont incohérents {$reservation->state} {$usrContract->pivot->payment_state}");
            $this->message = "Problème d'état avec la réservation, veuillez consulter les administrateurs.";
            return false;
        }

        $reservation->user->contracts()->updateExistingPivot($contractId, [
            'payment_state' => $newPivotState,
        ]);

        return true;
    }

    /**
     * Envoi les emails de confirmation
     */
    public function sendEmails(Reservation $reservation, $amount): void
    {
        \Log::info("{$reservation->reference} On envoi le mail à {$reservation->email} et " . env('MAIL_RESPONSABLE_RESA', ''));

        Mail::to($reservation->email)->send(new ConfirmResa($reservation, $amount));
        Mail::to(env('MAIL_RESPONSABLE_RESA', ''))->send(new NewResaForAdmin($reservation));
    }
}