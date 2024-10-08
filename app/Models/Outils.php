<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Outils extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
        'prix',
        'nombre',
        'duree',
        'conseil',
        'precaution',
        'categorie_id',
        'file_id',
        'file2_id'
    ];

    public function file() : HasOne{
        return $this->hasOne(File::class);
    }
}