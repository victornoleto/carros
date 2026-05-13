<?php

namespace App\Jobs\Webmotors;

use App\Enums\CarProviderEnum;
use App\Jobs\CarSyncJob;

class WebmotorsSyncJob extends CarSyncJob
{
    public static function provider(): CarProviderEnum
    {
        return CarProviderEnum::WEBMOTORS;
    }
}
