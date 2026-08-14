<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'category_id',
    'contract_id',
    'name',
    'description',
    'advice',
    'caution',
    'image',
    'icon',
    'active',
    'number',
    'view',])]
class Tool extends Model
{
    use HasFactory;
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function features()
    {
        return $this->hasMany(Feature::class);
    }
}