<?php

namespace App\Services;

use App\Models\Parameter;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Tool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;


class SrvReservation {

    private string $message="";
    public Reservation $reservation;


    /**
     * Création d'une réservation
     * Vérification que c'est possible avant
     */
    public function create(User $user, Tool $tool, $dstart, $dend,$comment ) : bool {
        if (!$user || !$tool) { 
            $message = "Internal error";
            return false;
        }

        // On verrouille dans une transaction pour éviter
        // deux réservations simultanées qui dépasseraient tool.number
        DB::transaction(function () use($user,$tool,$dstart,$dend,$comment) {
            $reservationsCount = Reservation::where('tool_id', $tool->id)
                ->where('state', '!=', 'cancelled')
                ->where('date_start', '<=', $dend)
                ->where('date_end', '>=', $dstart)
                ->lockForUpdate()
                ->count();

            if ($reservationsCount >= $tool->number) {
                $message = "Ce matériel n'est plus disponible sur cette période.";                
                return false;
            }

           $this->reservation = Reservation::create([
                'reference'      => 'LBO'.date('ym').rand(1000,9999),
                'tool_id'        => $tool->id,
                'user_id'        => $user->id,
                'name'           => $user->firstname . " " . $user->name,
                'email'          => $user->email,
                'phone'          => $user->phone,
                'date_start'     => $dstart,
                'date_end'       => $dend,
                'comment'        => $comment,
                'state'          => Reservation::STATE_RESERVED,
                'payment_state'  => Reservation::PAYMENT_STATE_UNPAID,
            ]);
        });        

        $message = "Réservation effectuée avec succès.";
        return true;
    }


    public function getMessage() {
        return $this->message;
    }

}