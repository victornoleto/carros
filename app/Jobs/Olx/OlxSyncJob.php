<?php

namespace App\Jobs\Olx;

use App\Enums\CarProviderEnum;
use App\Jobs\CarSyncJob;
use App\Services\Olx\OlxSyncService;

class OlxSyncJob extends CarSyncJob
{
    public function __construct(
        public string $brand,
        public string $model,
        public int $page = 1,
        public bool $recursive = false
    ) {
        parent::__construct(CarProviderEnum::OLX, $brand, $model, $page, $recursive);
    }

    public function getSyncService(): OlxSyncService
    {
        return new OlxSyncService();
    }

    public function getProcessJobClass(): string
    {
        return OlxProcessJob::class;
    }
}
