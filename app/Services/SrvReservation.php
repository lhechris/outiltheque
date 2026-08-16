<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Mail\ConfirmResa;
use App\Mail\NewResaForAdmin;
use App\Models\Contract;
use App\Models\Reservation;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * NOTE - hypothèses posées sur le modèle (à ajuster si les noms diffèrent) :
 *
 * - User belongsToMany(Contract::class)->withPivot('payment_state')->withTimestamps();
 *   nom de relation supposé : User::contracts()
 *   valeurs du pivot 'payment_state' supposées : 'paid' | 'unpaid'
 *   (remplace par les constantes réelles de Contract si elles existent,
 *   ex. Contract::PIVOT_PAID / Contract::PIVOT_UNPAID)
 *
 * - Contract::maxpermonth (existant) : quota mensuel, null = pas de limite mensuelle
 * - Contract::maxperyear (à ajouter en migration si besoin) : quota annuel, null = pas de limite annuelle
 * - Si maxpermonth ET maxperyear sont null => forfait illimité
 */
class SrvReservation
{
    private string $message = "";
    public Reservation $reservation;

    /**
     * Création d'une réservation.
     * Vérifie la disponibilité du matériel, et si le paiement demandé
     * est "forfait", vérifie l'éligibilité de l'utilisateur (contrat payé
     * + quota mensuel/annuel non dépassé) avant de créer la réservation.
     */
    public function create(User $user, Tool $tool, $dstart, $dend, $paiement, $comment): bool
    {
        $success = false;

        // On verrouille dans une transaction pour éviter deux réservations
        // simultanées qui dépasseraient tool.number.
        // $success est capturé par référence pour que create() reflète
        // réellement ce qui s'est passé dans la transaction.
        DB::transaction(function () use ($user, $tool, $dstart, $dend, $paiement, $comment, &$success) {

            \Log::debug("Creation d'une réservation pour {$user->email} {$tool->name} $dstart $dend $paiement");
            //on verifie si l'outil est disponible sur la période
            $reservationsCount = Reservation::where('tool_id', $tool->id)
                ->where('state', '!=', Reservation::STATE_CANCELLED)
                ->where('date_start', '<=', $dend)
                ->where('date_end', '>=', $dstart)
                ->lockForUpdate()
                ->count();

            if ($reservationsCount >= $tool->number) {
                \Log::debug("Pas poossible, il y a $reservationsCount reservations sur la période");
                $this->message = "Ce matériel n'est plus disponible sur cette période.";
                return;
            }

            $payment_state = Reservation::PAYMENT_STATE_UNPAID;
            $state = Reservation::STATE_RESERVED;

            //  pour un forfait on verifie le dépassement de quota
            if ($paiement === 'forfait') {
                \Log::debug("Paiement au forfait, on vérifie les quota");
                $payment_state = Reservation::PAYMENT_STATE_FORFAIT;
                if( $this->forfaitQuotaExceeded($user,$tool->contract,$dstart)) {
                    $this->message = "Vous avez dépasser le quota de réservation ({$tool->contract->restriction}).";
                    \Log::info("$user->email a depassé le quota ");
                    return;
                }

                //Si le forfait est payé on peut passer à confirmé
                $contract = $user->contracts()->firstWhere('contracts.id', $tool->contract->id);
                $ps = $contract?->pivot?->payment_state;
                if ($ps) {
                    \Log::info("Le forfait est payé (ou en cours de paiement) on confirme la résa de $user->email");
                    if ($ps !== Reservation::PAYMENT_STATE_UNPAID) {
                        $state = Reservation::STATE_CONFIRMED;
                        \Log::info("Le contrat pour $user->email est payé");
                    }
                } else {
                    //pas de contract pour cet user, il sera cree dans SrvPayment
                    \Log::info("Pas de contrat pour $user->email");
                }
            }

            $this->reservation = Reservation::create([
                'reference'     => 'LBO' . date('ym') . rand(1000, 9999),
                'tool_id'       => $tool->id,
                'user_id'       => $user->id,
                'name'          => $user->firstname . " " . $user->name,
                'email'         => $user->email,
                'phone'         => $user->phone,
                'date_start'    => $dstart,
                'date_end'      => $dend,
                'comment'       => $comment,
                'state'         => $state,
                'payment_state' => $payment_state,
            ]);

            if ($state == Reservation::STATE_CONFIRMED) {
                \Log::info("{$this->reservation->reference} On envoi le mail à {$this->reservation->email} et " . env('MAIL_RESPONSABLE_RESA', ''));
                Mail::to($this->reservation->email)->send(new ConfirmResa($this->reservation));
                Mail::to(env('MAIL_RESPONSABLE_RESA', ''))->send(new NewResaForAdmin($this->reservation));
            }

            $success = true;
            $this->message = "Réservation effectuée avec succès.";
        });

        return $success;
    }

    public function getMessage()
    {
        return $this->message;
    }

    // Annule une réservation
    public function cancel(Reservation $reservation): bool
    {
        $this->reservation = $reservation;

        if (auth()->user()->isAdmin()) {
            \Log::info("{$this->reservation->reference} Annulation de la resa par admin");
            $this->reservation->state = Reservation::STATE_CANCELLED;
            $this->reservation->setCancelled();

            $this->message = "Succès annulation de la réservation";
            return true;
        }

        if (auth()->user()->id === $this->reservation->user->id && $this->reservation->isReserved()) {
            \Log::info("{$this->reservation->reference} Annulation de la resa par {$this->reservation->user->firstname} {$this->reservation->name}");
            $this->reservation->state = Reservation::STATE_CANCELLED;
            $this->reservation->setCancelled();

            $this->message = "Succès annulation de la réservation";
            return true;
        }

        $this->message = "Vous n'êtes pas autorisé.";
        return false;
    }

    /**
     * Vérifie si l'utilisateur à un forfait pour la categorie de cet outil
     */
    public function isForfait(User $user, Tool $tool): bool
    {
        $contract = $tool->contract;

        $hasContract = $user->contracts()
            ->where('contracts.id', $contract->id)
            ->exists();

        return $hasContract;
    }

    /**
     * Vérifie si l'utilisateur doit payer cette réservation
     * - est ce que la réservation est réservée
     * - en fonction du type de paiement :
     *       - UNPAID : on demande le paiement
     *       - FORFAIT : s'il n'a pas de contract on demande le paiement
     */
    public function needToPay(Reservation $reservation): bool
    {
        if ($reservation->state != Reservation::STATE_RESERVED) {
            return false;
        }

        if ($reservation->payment_state == Reservation::PAYMENT_STATE_FORFAIT) {
            //Est ce qu'il exite un contract payé
            $hasPaidContract = $reservation->user->contracts()
                ->where('contracts.id', $reservation->tool->contract->id)
                ->wherePivotIn('payment_state', [Reservation::PAYMENT_STATE_HA_PAYED,Reservation::PAYMENT_STATE_PAYED])
                ->exists();
            return(!$hasPaidContract);

        } else {
            return($reservation->payment_state == Reservation::PAYMENT_STATE_UNPAID);
        }

    }

    /**
     * Détermine si l'utilisateur a atteint le quota de son forfait pour ce contrat.
     * Priorité : mensuel puis annuel ; si aucun des deux n'est défini => illimité.
     */
    private function forfaitQuotaExceeded(User $user, Contract $contract,string $dstart): bool
    {

        $maxPerMonth = null;
        $maxPerYear = null;
        $maxQuota = null;
        $windowDays = null;

        if (preg_match('/^(\d+)\s+par\s+(mois|an)$/i', (string)$contract->restriction, $m)) {
            match (strtolower($m[2])) {
                'mois' => $maxPerMonth = (int) $m[1],
                'an'   => $maxPerYear = (int) $m[1],
            };
        }

        if (preg_match('/^(\d+)\s+pendant\s+(\d+)\s+jours?$/i', (string)$contract->restriction, $m)) {
            $maxQuota = (int) $m[1];
            $windowDays = (int) $m[2];
        }

        if (is_null($maxPerMonth) && is_null($maxPerYear) && is_null($maxQuota) && is_null($windowDays)) {
            \Log::debug("Pas de quota");
            return false; // illimité
        }

        $dstart = Carbon::parse($dstart);

        $usedQuery = fn ($start, $end) => Reservation::where('user_id', $user->id)
            ->where('payment_state', Reservation::PAYMENT_STATE_FORFAIT)
            ->where('state', '!=', Reservation::STATE_CANCELLED)
            ->whereHas('tool', fn ($q) => $q->where('contract_id', $contract->id))
            ->whereBetween('date_start', [$start, $end])
            ->count();

        if (!is_null($maxPerMonth)) {
            $used = $usedQuery($dstart->copy()->startOfMonth(), $dstart->copy()->endOfMonth());
            return $used >= $maxPerMonth;
        }
        if (!is_null($maxPerYear)) {
            $used = $usedQuery($dstart->copy()->startOfYear(), $dstart->copy()->endOfYear());
            return $used >= $maxPerYear;
        }
        
        $ds=$dstart->copy()->subDays($windowDays);
        $de=$dstart->copy();

        $used = $usedQuery($ds,$de);
        return $used >= $maxQuota;

    }
}