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
        'prenom',
        'email',        
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

}