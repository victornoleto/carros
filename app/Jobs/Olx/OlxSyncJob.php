<?php

namespace App\Jobs\Olx;

use App\Enums\CarProviderEnum;
use App\Jobs\CarSyncJob;

class OlxSyncJob extends CarSyncJob
{
    public function getProvider(): CarProviderEnum
    {
        return CarProviderEnum::OLX();
    }
}
