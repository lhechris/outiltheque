<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalReservations extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'outil_id',
        'nom',
        'email',
        'telephone',
        'commentaire',
        'debut',
        'fin',
        'paiement_state',
        'state',
        'paiement_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'debut' => 'datetime:Y-m-d',
        'fin' => 'datetime:Y-m-d'
    ];


}