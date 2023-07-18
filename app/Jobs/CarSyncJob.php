<?php

namespace App\Jobs;

use App\Enums\CarProviderEnum;
use App\Models\Car;
use App\Traits\CarProviderTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

abstract class CarSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    use CarProviderTrait;

    public $tries = 3;

    public $timeout = 120;

    public $backoff = 60;

    public CarProviderEnum $provider;

    public function __construct(
        public string $brand,
        public string $model,
        public int $page = 1,
        public bool $recursive = false,
    ) {
        $this->setProviderByClassName();
    }

    public function handle(): void
    {
        try {

            $this->process();

        } catch (\Throwable $e) {

            $this->handleError($e);
        }
    }
    
    private function process(): void
    {
        //$this->log('Starting');
    
        if ($this->page == 1 && $this->recursive) {
            Car::disable($this->provider->value, $this->brand, $this->model);
        }
    
        $syncService = $this->provider->getSyncService();
    
        $startTime = microtime(true);
    
        $pageResults = $syncService->getPageEntireResults($this->brand, $this->model, $this->page);
    
        $unprocessedResults = $syncService->getPageUnprocessedResults($pageResults);
        
        $elapsedTime = round(microtime(true) - $startTime, 2);
    
        $this->log(sprintf('%s ads found in %s seconds', count($unprocessedResults), $elapsedTime));
    
        foreach ($unprocessedResults as $unprocessedResult) {
    
            $processJob = $this->provider->getProcessJob([
                'brand' => $this->brand,
                'model' => $this->model,
                'data' => $unprocessedResult,
            ]);
    
            dispatch($processJob)->onQueue('cars:process');
        }
    
        if (count($unprocessedResults) > 0 && $this->recursive) {
    
            if ($this->provider->value != CarProviderEnum::OLX) {
                sleep(1);
            }
                
            self::dispatch($this->brand, $this->model, $this->page + 1, true)->onQueue('cars:sync');
        }
    }

    private function handleError(\Throwable $e): void
    {
        $this->log($e->getMessage(), 'error');

        if ($this->attempts() < $this->tries) {

            $this->log(sprintf('Retrying in %s seconds', $this->backoff), 'error');
                
            $this->release($this->backoff);
        
        } else {
            $this->log('Max attempts reached', 'error');
        }
    }

    private function log(string $message, string $channel = 'debug'): void
    {
        $logMessage = sprintf(
            '[car-sync][%s][%s/%s][%s][%s] %s',
            $this->provider->value,
            $this->page,
            $this->attempts(),
            $this->brand,
            $this->model,
            $message
        );

        Log::$channel($logMessage);
    }
}
