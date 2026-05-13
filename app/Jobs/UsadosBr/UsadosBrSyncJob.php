<?php

namespace App\Jobs\UsadosBr;

use App\Enums\CarProviderEnum;
use App\Jobs\CarSyncJob;

class UsadosBrSyncJob extends CarSyncJob
{
    public static function provider(): CarProviderEnum
    {
        return CarProviderEnum::USADOSBR;
    }
}
