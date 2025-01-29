<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Helloasso;

class refreshHelloasso extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-helloasso';

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
        Helloasso::refreshToken();
    }
}
