<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caracoutils extends Model
{
    use HasFactory;

    protected $fillable = [
        'outil_id',
        'nom',
        'valeur'
    ];

}