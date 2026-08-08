<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use Mail;
use App\Mail\ResaDeLaSemaine;
use App\Models\Reservations;


#[Signature('app:mail-week')]
#[Description('Envoi le mail des réservations de la semaine')]
class AutoMailWeek extends Command
{
   /**
     * Execute the console command.
     */
    public function handle()
    {
        Mail::to(env('MAIL_RESPONSABLE_RESA',''))->send(new ResaDeLaSemaine(Reservations::listeResaSemaine(),Reservations::listeRetourSemaine()));
    }}
