<?php

namespace App\Console\Commands;

use App\Jobs\Olx\OlxCarSyncJob;
use Illuminate\Console\Command;

class CarSyncCommand extends Command
{
    protected $signature = 'car:sync {brand} {model}';

    public function handle()
    {
        $brand = $this->argument('brand');

        $model = $this->argument('model');

        OlxCarSyncJob::dispatch($brand, $model)
            ->onQueue('olx:sync');
    }
}
