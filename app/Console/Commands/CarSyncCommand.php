<?php

namespace App\Console\Commands;

use App\Enums\CarProviderEnum;
use Illuminate\Console\Command;

class CarSyncCommand extends Command
{
    protected $signature = 'car:sync {brand} {model}';

    public function handle()
    {
        $brand = $this->argument('brand');

        $model = $this->argument('model');

        $providers = CarProviderEnum::getInstances();

        /* $providers = [
            CarProviderEnum::ICARROS(),
        ]; */

        foreach ($providers as $provider) {

            /* if ($provider->value == CarProviderEnum::ICARROS) {
                return;
            } */

            $jobClass = $provider->getSyncJobClass();

            $job = app($jobClass, [
                'provider' => $provider->value,
                'brand' => $brand,
                'model' => $model,
                'page' => 1,
                'recursive' => true,
            ]);

            dispatch($job)
                ->onQueue($provider->getSyncQueueName());
        }
    }
}
