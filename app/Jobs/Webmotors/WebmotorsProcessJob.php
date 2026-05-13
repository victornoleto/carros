<?php

namespace App\Jobs\Webmotors;

use App\Enums\CarProviderEnum;
use App\Jobs\CarProcessJob;

class WebmotorsProcessJob extends CarProcessJob
{
    public static function provider(): CarProviderEnum
    {
        return CarProviderEnum::WEBMOTORS;
    }
}
