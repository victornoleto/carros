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

/**
 * Sincronizar anúncios de determinada marca e modelo de carro em um provedor.
 *
 * @author Victor Noleto <victornoleto@sysout.com.br>
 * @since 11/07/2023 
 * @version 1.0.0
 */
class CarSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $brand,
        public string $model,
        public string $provider,
    )
    {
    }

    public function handle(): void
    {
        $enumInstance = CarProviderEnum::fromValue($this->provider);

        Car::disable($enumInstance->key);

        $service = $enumInstance->getService();

        $service->sync($this->brand, $this->model);
    }
}
