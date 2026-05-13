<?php

namespace App\Jobs\Olx;

use App\Enums\CarProviderEnum;
use App\Jobs\CarSyncJob;

class OlxSyncJob extends CarSyncJob
{
    public static function provider(): CarProviderEnum
    {
        return CarProviderEnum::OLX;
    }
}
