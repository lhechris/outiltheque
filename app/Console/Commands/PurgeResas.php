<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservations;

class PurgeResas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:purge-resas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Reservations::historise();
    }
}
