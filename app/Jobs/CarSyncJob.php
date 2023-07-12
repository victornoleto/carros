<?php

namespace App\Jobs;

use App\Enums\CarProviderEnum;
use App\Models\Car;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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

    abstract public function getProvider(): CarProviderEnum;

    public function handle(): void
    {
        $this->log('Starting');

        $provider = $this->getProvider();

        if ($this->page == 1 && $this->recursive) {
            Car::disable($provider->value, $this->brand, $this->model);
        }

        $syncService = $provider->getSyncService();

        $startTime = microtime(true);

        $pageResult = $syncService->getPageResult($this->brand, $this->model, $this->page);

        $elapsedTime = round(microtime(true) - $startTime, 2);

        $this->log(sprintf('Page result received in %s seconds', $elapsedTime));

        $adResults = $syncService->getAdResults($pageResult);

        $this->log(sprintf('Found %s ads', count($adResults)));

        foreach ($adResults as $adResult) {

            $processJobClass = $provider->getProcessJobClass();

            $processJob = app($processJobClass, [
                'brand' => $this->brand,
                'model' => $this->model,
                'adResult' => $adResult,
            ]);

            dispatch($processJob)
                ->onQueue($provider->getProcessQueueName());
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

    private function log(string $message, string $channel = 'debug'): void
    {
        $provider = $this->getProvider();

        $logMessage = sprintf(
            '[car-sync][%s][%s][%s][%s] %s',
            $provider->value,
            $this->brand,
            $this->model,
            $this->page,
            $message
        );

        Log::$channel($logMessage);
    }
}
