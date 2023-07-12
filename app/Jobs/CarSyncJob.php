<?php

namespace App\Jobs;

use App\Enums\CarProviderEnum;
use App\Models\Car;
use App\Services\CarSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class CarSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $provider,
        public string $brand,
        public string $model,
        public int $page = 1,
        public bool $recursive = false
    ) {
    }

    public function handle(): void
    {
        $provider = CarProviderEnum::fromValue($this->provider);

        if ($this->page == 1 && $this->recursive) {
            Car::disable($this->provider, $this->brand, $this->model);
        }

        $service = $this->getSyncService();

        $pageResult = $service->getPageResult($this->brand, $this->model, $this->page);

        $adResults = $service->getAdResults($pageResult);

        foreach ($adResults as $adResult) {

            $jobClass = $this->getProcessJobClass();

            $job = app($jobClass, [
                'brand' => $this->brand,
                'model' => $this->model,
                'adResult' => $adResult,
            ]);

            $job->onQueue($provider->getProcessQueueName());
        }

        if (count($adResults) > 0 && $this->recursive) {
                
            self::dispatch($this->brand, $this->model, $this->page + 1, true)
                ->onQueue($provider->getSyncQueueName());
        }
    }

    abstract public function getSyncService(): CarSyncService;

    abstract public function getProcessJobClass(): string;
}
