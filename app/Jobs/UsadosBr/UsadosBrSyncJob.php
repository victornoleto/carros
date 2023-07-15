<?php

namespace App\Jobs\UsadosBr;

use App\Enums\CarProviderEnum;
use App\Jobs\CarSyncJob;

class UsadosBrSyncJob extends CarSyncJob
{
    public function getProvider(): CarProviderEnum
    {
        return CarProviderEnum::USADOSBR();
    }
}
