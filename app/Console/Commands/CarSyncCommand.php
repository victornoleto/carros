<?php

namespace App\Console\Commands;

use App\Jobs\Olx\OlxSyncJob;
use App\Jobs\Webmotors\WebmotorsSyncJob;
use App\Models\Car;
use Illuminate\Console\Command;

class CarSyncCommand extends Command
{
    protected $signature = 'car:sync {brand} {model}';

    public function handle()
    {
        $brand = $this->argument('brand');

        $model = $this->argument('model');

        /*Car::disable($brand, $model);

        OlxSyncJob::dispatch($brand, $model)
            ->onQueue('olx:sync');*/

        WebmotorsSyncJob::dispatchSync($brand, $model);
        //->onQueue('webmotors:sync');
    }
}
