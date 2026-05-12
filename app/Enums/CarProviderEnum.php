<?php

declare(strict_types=1);

namespace App\Enums;

use App\Jobs\CarProcessJob;
use App\Jobs\CarSyncJob;
use App\Jobs\iCarros\iCarrosProcessJob;
use App\Jobs\iCarros\iCarrosSyncJob;
use App\Jobs\Olx\OlxProcessJob;
use App\Jobs\Olx\OlxSyncJob;
use App\Jobs\UsadosBr\UsadosBrProcessJob;
use App\Jobs\UsadosBr\UsadosBrSyncJob;
use App\Jobs\Webmotors\WebmotorsProcessJob;
use App\Jobs\Webmotors\WebmotorsSyncJob;
use App\Services\CarProcessService;
use App\Services\CarSyncService;
use App\Services\iCarros\iCarrosProcessService;
use App\Services\iCarros\iCarrosSyncService;
use App\Services\Olx\OlxProcessService;
use App\Services\Olx\OlxSyncService;
use App\Services\UsadosBr\UsadosBrProcessService;
use App\Services\UsadosBr\UsadosBrSyncService;
use App\Services\Webmotors\WebmotorsProcessService;
use App\Services\Webmotors\WebmotorsSyncService;

enum CarProviderEnum: string
{
    case OLX = 'olx';
    case WEBMOTORS = 'webmotors';
    case ICARROS = 'icarros';
    case USADOSBR = 'usadosbr';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getValues(): array
    {
        return self::values();
    }

    public static function getInstances(): array
    {
        return self::cases();
    }

    public static function fromValue(string $value): self
    {
        return self::from($value);
    }

    public function getSyncService(array $parameters = []): CarSyncService
    {
        return $this->getClass('syncService', $parameters);
    }

    public function getSyncJob(array $parameters = []): CarSyncJob
    {
        return $this->getClass('syncJob', $parameters);
    }

    public function getProcessService(array $parameters = []): CarProcessService
    {
        return $this->getClass('processService', $parameters);
    }

    public function getProcessJob(array $parameters = []): CarProcessJob
    {
        return $this->getClass('processJob', $parameters);
    }

    public function getUrl(): string
    {
        return $this->config()['url'];
    }

    private function getClass(string $key, array $parameters = []): mixed
    {
        $className = $this->config()[$key];

        $class = app($className, $parameters);

        return $class;
    }

    private function config(): array
    {
        return match ($this) {
            self::OLX => [
                'url' => 'https://www.olx.com.br',
                'syncService' => OlxSyncService::class,
                'syncJob' => OlxSyncJob::class,
                'processService' => OlxProcessService::class,
                'processJob' => OlxProcessJob::class,
            ],
            self::WEBMOTORS => [
                'url' => 'https://www.webmotors.com.br',
                'syncService' => WebmotorsSyncService::class,
                'syncJob' => WebmotorsSyncJob::class,
                'processService' => WebmotorsProcessService::class,
                'processJob' => WebmotorsProcessJob::class,
            ],
            self::ICARROS => [
                'url' => 'https://www.icarros.com.br',
                'syncService' => iCarrosSyncService::class,
                'syncJob' => iCarrosSyncJob::class,
                'processService' => iCarrosProcessService::class,
                'processJob' => iCarrosProcessJob::class,
            ],
            self::USADOSBR => [
                'url' => 'https://www.usadosbr.com',
                'syncService' => UsadosBrSyncService::class,
                'syncJob' => UsadosBrSyncJob::class,
                'processService' => UsadosBrProcessService::class,
                'processJob' => UsadosBrProcessJob::class,
            ],
        };
    }
}
