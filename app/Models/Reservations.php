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


}