<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

use App\Services\Helloasso\Token;

#[Signature('app:init-helloasso')]
#[Description('Initialise le access token helloasso')]
class initHelloasso extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        Token::init();
    }
}
