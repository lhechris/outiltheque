<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservations extends Model
{
    use HasFactory;

    protected $fillable = [
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

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'emprunt' => 'datetime:Y-m-d'
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
    

}