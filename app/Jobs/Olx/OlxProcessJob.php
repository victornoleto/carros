<?php

namespace App\Jobs\Olx;

use App\Enums\CarProviderEnum;
use App\Jobs\CarProcessJob;
use App\Models\Car;
use App\Services\Olx\OlxSyncService;

class OlxProcessJob extends CarProcessJob
{
    public function __construct(
        string $brand,
        string $model,
        public string $adResult
    ) {
        parent::__construct($brand, $model, CarProviderEnum::OLX, $adResult);
    }
    
    public function getAdData(): array
    {
        $service = new OlxSyncService();
    
        $data = $service->getAdData($this->brand, $this->model, $this->adResult);

        return $data;
    }

    public function onCarSaved(Car $car): void
    {
        OlxUpdateJob::dispatch($car)
            ->onQueue('olx:update'); // TODO alterar para buscar job e fila dinamicamente
    }
}
