<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalReservations extends Model
{
    use HasFactory;

    protected $fillable = [
        'outil_id',
        'user_id',
        'debut',
        'fin'
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