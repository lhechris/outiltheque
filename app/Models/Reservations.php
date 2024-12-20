<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\JournalReservations;

class Reservations extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'outil_id',
        'nom',
        'email',
        'telephone',       
        'debut',
        'fin',
        'state',
        'paiement_state',
        'paiement_id',
        'commentaire'

    ];

    public const PAIEMENT_STATE_NON_PAYE = "Non payé";
    public const PAIEMENT_STATE_A_PAYER = "A payer";
    public const PAIEMENT_STATE_HA_ENCOURS = "helloasso en cours";
    public const PAIEMENT_STATE_HA_PAYE = "Payé Helloasso";
    public const PAIEMENT_STATE_PAYE = "Payé en espèce";

    public const STATE_RESERVE = "Réservé";
    public const STATE_PAIEMENT = "Paiement";
    public const STATE_CONFIRME = "Confirmé";
    public const STATE_ANNULE = "Annulé";


    /**
     * Evenement before create
     */
    public static function boot()
    {
        parent::boot();

        self::creating(function($model){
            $model['reference'] = 'LBO'.date('ym').rand(1000,9999);
        });
    } 


    /**
     * 
     */
    public static function est_possible($outil_id,$debut,$fin) {
        //Verifie la disponibilite
        $data = self::leftjoin("outils","reservations.outil_id","=","outils.id")
                                ->select("outils.nombre")
                                ->where('reservations.outil_id', $outil_id)
                                ->whereDate('fin','>=',$debut)
                                ->whereDate('debut','<=',$fin);
        //\Log::info($data->toRawSql());
        $res=$data->get();

        /*$nb=0;
        $nombre=0;
        foreach ($data->get() as $d) {
            $nb++;
            $nombre= $d->nombre;
            \Log::info(print_r($d,true));
        }
        if (($nb>0) && ($nb>=$nombre)) { */
        if ((count($res)>0) && ($res[0]->nombre<=count($res))) {

            \Log::info("Outil $outil_id reservé ".count($res)." fois sur ".$res[0]->nombre);
            return false;                
        }
        return true;
    }  

    private function dateToFrench($date, $format) 
    {
        $english_days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
        $french_days = array('lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche');
        $english_months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
        $french_months = array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre');
        
        $d= date_create($date);
        $f = date_format($d,$format);
        return str_replace($english_months, $french_months, str_replace($english_days, $french_days, $f ) );
    }
    
    public function debutfrench() 
    {
        return $this->dateToFrench($this->debut,'l j F Y');
    }
    public function finfrench() 
    {
        return $this->dateToFrench($this->fin,'l j F Y');
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
            JournalReservations::create($d->toArray());
            $d->delete();            
        }
    }

}



