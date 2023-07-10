<?php

namespace App\Console\Commands;

use App\Models\OlxCar;
use App\Services\OlxService;
use Illuminate\Console\Command;

class OlxSyncCommand extends Command
{
    protected $signature = 'olx:sync {brand=mitsubishi} {model=lancer}';

    public function handle()
    {
        $brand = mb_strtolower($this->argument('brand'));

        $model = mb_strtolower($this->argument('model'));

        OlxCar::query()
            ->where('brand', $brand)
            ->where('model', $model)
            ->update([
                'active' => false
            ]);

        $service = new OlxService();

        $service->sync($brand, $model);
    }
}
