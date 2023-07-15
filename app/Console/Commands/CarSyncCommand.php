<?php

namespace App\Console\Commands;

use App\Enums\CarProviderEnum;
use Illuminate\Console\Command;

class CarSyncCommand extends Command
{
    protected $signature = 'car:sync {brand} {model} {provider?}';

    public function handle()
    {
        $brand = $this->argument('brand');

        $model = $this->argument('model');

        $provider = $this->argument('provider');

        $providers = CarProviderEnum::getInstances();

        foreach ($providers as $providerInstance) {

            if ($providerInstance->value == CarProviderEnum::ICARROS) {
                return;
            }

            if ($provider && $providerInstance->value != $provider) {
                continue;
            }

            $jobClass = $providerInstance->getSyncJobClass();

            $job = app($jobClass, [
                'provider' => $providerInstance->value,
                'brand' => $brand,
                'model' => $model,
                'page' => 1,
                'recursive' => true,
            ]);

            dispatch($job)
                ->onQueue($providerInstance->getSyncQueueName());
        }
    }
}
