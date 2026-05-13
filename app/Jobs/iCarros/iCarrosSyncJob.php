<?php

namespace App\Jobs\iCarros;

use App\Enums\CarProviderEnum;
use App\Jobs\CarSyncJob;

class iCarrosSyncJob extends CarSyncJob
{
    public static function provider(): CarProviderEnum
    {
        return CarProviderEnum::ICARROS;
    }
}
