<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Categories extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description'
    ];

}