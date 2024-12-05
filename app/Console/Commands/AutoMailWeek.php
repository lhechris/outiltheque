<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mail;
use App\Mail\ResaDeLaSemaine;
use App\Models\Reservations;

class AutoMailWeek extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mail-week';

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
        Mail::to(env('MAIL_RESPONSABLE_RESA',''))->send(new ResaDeLaSemaine(Reservations::listeResaSemaine(),Reservations::listeRetourSemaine()));
    }
}
