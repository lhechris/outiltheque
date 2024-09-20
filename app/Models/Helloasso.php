<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Helloasso extends Model
{
    use HasFactory;

    protected $table = 'helloasso';

    protected $fillable = [
        'nom',
        'valeur'
    ];

}