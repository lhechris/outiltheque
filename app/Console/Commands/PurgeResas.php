<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Reservations;

#[Signature('app:purge-resas')]
#[Description("Supprime les réservations dans des états d''attente")]
class PurgeResas extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        Reservations::historise();
    }
}
