<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Ce model pour pouvoir etre geré par filamanent
 */
class Subscription extends Pivot
{
    protected $table = 'contract_user';

    protected $fillable = [
        'contract_id',
        'user_id',
        'payment_state',
        'begin',
        'expire',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }}