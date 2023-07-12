<?php

namespace App\Jobs\Webmotors;

use App\Enums\CarProviderEnum;
use App\Models\Car;
use App\Services\WebmotorsService;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Processar páginas de anúncios da Webmotors.
 *
 * @author Victor Noleto <noletovasco@gmail.com>
 * @since 12/07/2023
 * @version 1.0.0
 */
class WebmotorsSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        public string $brand,
        public string $model,
        public int $page = 1
    ) {
    }

    public function handle(): void
    {
        try {

            $service = new WebmotorsService();
    
            $service->sync($this->brand, $this->model, $this->page);

        } catch (\Throwable $e) {
            
            if ($this->attempts() > 3) {
                throw $e;
            }

            if ($e instanceof ClientException && $e->getCode() == Response::HTTP_UNAUTHORIZED) {

                Log::debug("[webmotors][sync][$this->brand][$this->model] Page #$this->page unauthorized. Waiting 10 minutes to retry...");

                $this->release(10 * 60);
                return;
            }

            $this->release(60);
        }
    }
}
