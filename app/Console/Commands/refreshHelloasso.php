<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

use App\Services\Helloasso\Token;

#[Signature('app:refresh-helloasso')]
#[Description('Met a jour le refresh token qui a une duree de vie de 30j')]
class refreshHelloasso extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        Token::refresh();
    }
}
