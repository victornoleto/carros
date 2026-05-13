<?php

namespace App\Jobs\Olx;

use App\Enums\CarProviderEnum;
use App\Jobs\CarProcessJob;

class OlxProcessJob extends CarProcessJob
{
    public static function provider(): CarProviderEnum
    {
        return CarProviderEnum::OLX;
    }
}
