<?php

namespace App\Jobs\Webmotors;

use App\Enums\CarProviderEnum;
use App\Jobs\CarSyncJob;

class WebmotorsSyncJob extends CarSyncJob
{
    public function getProvider(): CarProviderEnum
    {
        return CarProviderEnum::WEBMOTORS();
    }
}
