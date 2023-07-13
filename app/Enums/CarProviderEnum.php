<?php declare(strict_types=1);

namespace App\Enums;

use App\Jobs\iCarros\iCarrosSyncJob;
use App\Jobs\Olx\OlxProcessJob;
use App\Jobs\Olx\OlxSyncJob;
use App\Jobs\Webmotors\WebmotorsProcessJob;
use App\Jobs\Webmotors\WebmotorsSyncJob;
use App\Services\CarSyncService;
use App\Services\iCarros\iCarrosProcessService;
use App\Services\iCarros\iCarrosSyncService;
use App\Services\Olx\OlxProcessService;
use App\Services\Olx\OlxSyncService;
use App\Services\Webmotors\WebmotorsProcessService;
use App\Services\Webmotors\WebmotorsSyncService;
use BenSampo\Enum\Enum;

final class CarProviderEnum extends Enum
{
    const OLX = 'olx';

    const WEBMOTORS = 'webmotors';

    const ICARROS = 'icarros';

    public function getSyncService(): CarSyncService
    {
        switch ($this->value) {
            
            case self::OLX:
                return new OlxSyncService();
            
            case self::WEBMOTORS:
                return new WebmotorsSyncService();
            
            case self::ICARROS:
                return new iCarrosSyncService();

            default:
                throw new \Exception("Sync service not found for $this->value provider.");
        }
    }

    public function getProcessServiceClass(): string
    {

        switch ($this->value) {
            
            case self::OLX:
                return OlxProcessService::class;
            
            case self::WEBMOTORS:
                return WebmotorsProcessService::class;
            
            case self::ICARROS:
                return iCarrosProcessService::class;

            default:
                throw new \Exception("Process service class not found for $this->value provider.");
        }
    }

    public function getSyncJobClass(): string
    {

        switch ($this->value) {
            
            case self::OLX:
                return OlxSyncJob::class;
            
            case self::WEBMOTORS:
                return WebmotorsSyncJob::class;
            
            case self::ICARROS:
                return iCarrosSyncJob::class;

            default:
                throw new \Exception("Sync job class not found for $this->value provider.");
        }
    }

    public function getProcessJobClass(): string
    {

        switch ($this->value) {
            
            case self::OLX:
                return OlxProcessJob::class;
            
            case self::WEBMOTORS:
                return WebmotorsProcessJob::class;
            
            case self::ICARROS:
                return iCarrosSyncJob::class;

            default:
                throw new \Exception("Process job class not found for $this->value provider.");
        }
    }

    public function getSyncQueueName(): string
    {
            
        return $this->value.'-sync';
    }

    public function getProcessQueueName(): string
    {
            
        return $this->value.'-process';
    }

    public function getUpdateQueueName(): string
    {
            
        return $this->value.'-update';
    }
}
