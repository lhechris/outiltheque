<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name','description'])]
class Category extends Model
{
    public function tools()
    {
        return $this->hasMany(Tool::class);
    }
}

