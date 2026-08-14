<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
        'reference',
        'tool_id',
        'user_id',
        'name',
        'email',
        'phone',       
        'date_start',
        'date_end',
        'state',
        'payment_state',
        'payment_id',
        'comment'

    ])]
class Reservation extends Model
{
    public const PAYMENT_STATE_UNPAID = "Non payé";
    public const PAYMENT_STATE_FORFAIT = "Forfait";
    public const PAYMENT_STATE_TO_PAY = "A payer";
    public const PAYMENT_STATE_HA_PENDING = "helloasso en cours";
    public const PAYMENT_STATE_HA_PAYED = "Payé Helloasso";
    public const PAYMENT_STATE_PAYED = "Payé en espèce";

    public const STATE_RESERVED = "Réservé";
    public const STATE_PAYMENT = "Paiement";
    public const STATE_CONFIRMED = "Confirmé";
    public const STATE_CANCELLED = "Annulé";


    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory;


    public function tool() {
        return $this->belongsTo(Tool::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public static function listeResaSemaine() {
        $startweek = now()->StartOfWeek()->format('Y-m-d');
        $endweek = now()->EndOfWeek()->format('Y-m-d');

        //\DB::enableQueryLog();
        $data = self::leftjoin("outils","reservations.outil_id","=","outils.id")
                      ->select("reservations.*","outils.nom as nomoutil")
                      ->whereBetween("debut", [$startweek,$endweek])
                      ->get();
        //echo $now->format('Y-m-d');
        //print_r(\DB::getQueryLog(),false);
        return $data;        
    }

    public static function listeRetourSemaine() {
        $startweek = now()->StartOfWeek()->format('Y-m-d');
        $endweek = now()->EndOfWeek()->format('Y-m-d');
        
        //\DB::enableQueryLog();
        $data = self::leftjoin("outils","reservations.outil_id","=","outils.id")
                      ->select("reservations.*","outils.nom as nomoutil")
                      ->whereBetween("fin", [$startweek,$endweek])
                      ->get();
        //echo $now->format('Y-m-d');
        //print_r(\DB::getQueryLog(),false);
        return $data;        
    }

    /**
     * Supprime les réservations non confirmées et trop vieilles
     */
    public static function purge() {
 
        $fin = now()->subMinutes(15)->format('Y-m-d H:i:s');
        $data = self::where("state","=",self::STATE_RESERVE)
                    ->where("updated_at","<=",$fin)
                    ->get();

        foreach ($data as $d) {
            $d->delete();            
        }
    }

        /**
     * Supprime les réservations non confirmées et trop vieilles
     */
    public static function historise() {
 
        $fin = now()->format('Y-m-d');
        $data = self::where("fin","<",$fin)
                    ->get();

        foreach ($data as $d) {
            JournalReservation::create([
                'reference' => $d->reference,
                'tool_name'=> $d->tool->name,
                'name'=> $d->name,
                'email'=> $d->email,
                'phone'=> $d->phone,
                'date_start'=> $d->date_start,
                'date_end'=> $d->date_end,
                'state'=> $d->state,
                'payment_state'=> $d->payment_state,
                'payment_id'=> $c->payment_id,
                'comment'=> $c->comment,
            ]);
            $d->delete();          
        }
    }

    //helpers
    public function isReserved() { 
        return $this->state == Reservation::STATE_RESERVED;
    }
    public function setReserved() {
        $this->update(["state" => Reservation::STATE_RESERVED]);
        \Log::info("$this->reference Status = ".$this->state." Paiement status=".$this->payment_state);
    }
  
    // Annule une réservation
    // Historise cette resa et la supprime
    public function setCancelled() {
        \Log::info("$this->reference Annulation de la resa");

        JournalReservation::create([
            'reference' => $this->reference,
            'tool_name'=> $this->tool->name,
            'name'=> $this->name,
            'email'=> $this->email,
            'phone'=> $this->phone,
            'date_start'=> $this->date_start,
            'date_end'=> $this->date_end,
            'state'=> Reservation::STATE_CANCELLED,
            'payment_state'=> $this->payment_state,
            'payment_id'=> $this->payment_id,
            'comment'=> $this->comment,
        ]);

        $this->delete();                

    }

    public function isPayment() { 
        return $this->state == Reservation::STATE_PAYMENT;
    }

    public function setPaymentCash() {
        $this->state = Reservation::STATE_CONFIRMED;
        $this->payment_state = Reservation::PAYMENT_STATE_TO_PAY;
        $this->update();

        \Log::info("$this->reference Status = ".$this->state." Paiement status=".$this->payment_state);
    }
    
    public function isToPay() { 
        return $this->payment_state == Reservation::PAYMENT_STATE_TO_PAY;
    }

    public function isPendingHA() {  
        return $this->payment_state == Reservation::PAYMENT_STATE_HA_PENDING;      
    }    
    public function setPendingHA($haPayId) {        
        $this->update([
            "state" => Reservation::STATE_PAYMENT,
            "payment_state" => Reservation::PAYMENT_STATE_HA_PENDING,
            "payment_id" => $haPayId]);

        \Log::info("$this->reference Status = ".$this->state." Paiement status=".$this->payment_state." ID=".$haPayId);
    }

    public function setPaymentHA() {
        $this->state = Reservation::STATE_CONFIRMED;
        $this->payment_state = Reservation::PAYMENT_STATE_HA_PAYED;
        $this->update();

        \Log::info("$this->reference Status = ".$this->state." Paiement status=".$this->payment_state);

    }

    public function isConfirmed() { 
        return $this->state == Reservation::STATE_CONFIRMED;
    }

    /**
     * Retourne la liste des Status sous forme de collection
     */
    public static function states() {
        return [
            self::STATE_RESERVED => "Réservé",
            self::STATE_PAYMENT => "Paiement",
            self::STATE_CONFIRMED => "Confirmé",
            self::STATE_CANCELLED => "Annulé"
        ];
    }

    /**
     * Retourne la liste des Status de paiement sous forme de collection
     */
    public static function payment_states() {
        return [
            self::PAYMENT_STATE_UNPAID => "Non payé",
            self::PAYMENT_STATE_TO_PAY => "A payer",
            self::PAYMENT_STATE_HA_PENDING => "helloasso en cours",
            self::PAYMENT_STATE_HA_PAYED => "Payé Helloasso",
            self::PAYMENT_STATE_PAYED => "Payé en espèce",
            self::PAYMENT_STATE_FORFAIT => "Forfait",
        ];
    }

    public static function GetpaymentStateColor($state): string
    {
        return match ($state) {
            self::PAYMENT_STATE_UNPAID => 'red',
            self::PAYMENT_STATE_TO_PAY => 'amber',
            self::PAYMENT_STATE_HA_PENDING => 'amber',
            self::PAYMENT_STATE_HA_PAYED, Reservation::PAYMENT_STATE_PAYED => 'emerald',
            self::PAYMENT_STATE_FORFAIT => 'indigo',
            default => 'gray',
        };
    }

}
