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
        try {

            $this->process();

        } catch (\Throwable $e) {

            $this->log($e->getMessage(), 'error');

            if ($this->attempts() < $this->tries) {

                $backoff = $this->backoff()[$this->attempts() - 1] ?? 60;

                $this->log(sprintf('Retrying in %s seconds', $backoff), 'error');
                    
                $this->release($backoff);
            
            } else {
                $this->log('Max attempts reached', 'error');
            }
        }
    }
    
    public function backoff(): array
    {
        return [60, 180, 300];
    }
    
    private function process(): void
    {
        
        $this->log('Starting');
    
        $provider = $this->getProvider();
    
        if ($this->page == 1 && $this->recursive) {
            Car::disable($provider->value, $this->brand, $this->model);
        }
    
        $syncService = $provider->getSyncService();
    
        $startTime = microtime(true);
    
        $pageResult = $syncService->getPageResult($this->brand, $this->model, $this->page);
    
        $adResults = $syncService->getAdResults($pageResult);
        
        $elapsedTime = round(microtime(true) - $startTime, 2);
    
        $this->log(sprintf('%s ads found in %s seconds', count($adResults), $elapsedTime));
    
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
    
            if ($provider->value != CarProviderEnum::OLX) {
                sleep(1);
            }
                
            self::dispatch($this->brand, $this->model, $this->page + 1, true)
                ->onQueue($provider->getSyncQueueName());
        }
    }

    private function log(string $message, string $channel = 'debug'): void
    {
        $provider = $this->getProvider();

        $attempt = $this->attempts();

        $logMessage = sprintf(
            '[car-sync][%s][%s][%s][%s][%s] %s',
            $provider->value,
            $attempt,
            $this->brand,
            $this->model,
            $this->page,
            $message
        );

        Log::$channel($logMessage);
    }
}
