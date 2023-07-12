<?php

namespace App\Jobs\Webmotors;

use App\Enums\CarProviderEnum;
use App\Models\Car;
use App\Services\WebmotorsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
        if ($this->page == 1) {
            Car::disable(CarProviderEnum::WEBMOTORS);
        }

        $service = new WebmotorsService();

        $service->sync($this->brand, $this->model, $this->page);
    }
}
