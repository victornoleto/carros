<?php

namespace App\Console\Commands;

use App\Enums\CarProviderEnum;
use Illuminate\Console\Command;

class CarsSyncCommand extends Command
{
    protected $signature = 'cars:sync {brand} {model} {provider?}';

    public function handle()
    {
        $brand = $this->argument('brand');

        $model = $this->argument('model');

        $providerValue = $this->argument('provider');

        $providers = CarProviderEnum::getInstances();

        foreach ($providers as $provider) {

            if ($providerValue && $providerValue != $provider->value) {
                continue;
            }

            // TODO Reativar iCarros e UsadosBR no futuro
            if (in_array($provider->value, [CarProviderEnum::ICARROS, CarProviderEnum::USADOSBR])) {
                continue;
            }

            $this->comment("Syncing {$provider->value} {$brand} {$model}...");

            $job = $provider->getSyncJob([
                'brand' => $brand,
                'model' => $model,
                'page' => 1,
                'recursive' => true,
            ]);

            dispatch($job)->onQueue('cars-sync');
        }
    }
}
