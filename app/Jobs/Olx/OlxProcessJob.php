<?php

namespace App\Jobs\Olx;

use App\Enums\CarProviderEnum;
use App\Jobs\CarProcessJob;
use App\Models\Car;

class OlxProcessJob extends CarProcessJob
{
    public function getProvider(): CarProviderEnum
    {
        return CarProviderEnum::OLX();
    }

    public function onCarSaved(Car $car): void
    {
        $provider = $this->getProvider();

        OlxUpdateJob::dispatch($car)
            ->onQueue($provider->getUpdateQueueName());
    }
}
