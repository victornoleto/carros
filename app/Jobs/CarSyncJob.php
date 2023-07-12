<?php

namespace App\Jobs;

use App\Enums\CarProviderEnum;
use App\Models\Car;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class CarSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 120;

    public $failOnTimeout = true;

    public function __construct(
        public string $brand,
        public string $model,
        public int $page = 1,
        public bool $recursive = false
    ) {
    }

    public function handle(): void
    {
        $provider = $this->getProvider();

        if ($this->page == 1 && $this->recursive) {
            Car::disable($provider->value, $this->brand, $this->model);
        }

        $syncService = $provider->getSyncService();

        $pageResult = $syncService->getPageResult($this->brand, $this->model, $this->page);

        $adResults = $syncService->getAdResults($pageResult);

        foreach ($adResults as $adResult) {

            $processJobClass = $provider->getProcessJobClass();

            $processJob = app($processJobClass, [
                'brand' => $this->brand,
                'model' => $this->model,
                'adResult' => $adResult,
            ]);

            $processJob->onQueue($provider->getProcessQueueName());
        }

        if (count($adResults) > 0 && $this->recursive) {
                
            self::dispatch($this->brand, $this->model, $this->page + 1, true)
                ->onQueue($provider->getSyncQueueName());
        }
    }

    public function backoff(): array
    {
        return [60, 180, 300];
    }

    abstract public function getProvider(): CarProviderEnum;
}
