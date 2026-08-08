<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
        'reference',
        'tool_name',
        'name',
        'email',
        'phone',       
        'date_start',
        'date_end',
        'state',
        'payment_state',
        'payment_id',
        'comment'

    ])]
class JournalReservation extends Model
{
    //
}
