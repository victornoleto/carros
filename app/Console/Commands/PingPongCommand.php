<?php

namespace App\Console\Commands;

use App\Jobs\PingPongJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PingPongCommand extends Command
{
    protected $signature = 'ping-pong';

    public function handle()
    {
        Log::debug('Ping!');

        PingPongJob::dispatch();
    }
}
