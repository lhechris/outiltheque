<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name','price','color','restriction'])]
class Contract extends Model
{
    public function tools()
    {
        return $this->hasMany(Tool::class);
    }
}
