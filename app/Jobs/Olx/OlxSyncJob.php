<?php

namespace App\Jobs\Olx;

use App\Enums\CarProviderEnum;
use App\Models\Car;
use App\Services\OlxService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Processar páginas de anúncios da OLX.
 *
 * @author Victor Noleto <victornoleto@sysout.com.br>
 * @since 11/07/2023
 * @version 1.0.0
 */
class OlxSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $brand,
        public string $model,
        public int $page = 1
    )
    {
    }

    public function handle(): void
    {
        $service = new OlxService();

        $service->sync($this->brand, $this->model, $this->page);
    }
}
