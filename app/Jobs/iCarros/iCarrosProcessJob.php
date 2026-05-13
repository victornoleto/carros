<?php

namespace App\Jobs\iCarros;

use App\Enums\CarProviderEnum;
use App\Jobs\CarProcessJob;

class iCarrosProcessJob extends CarProcessJob
{
    public static function provider(): CarProviderEnum
    {
        return CarProviderEnum::ICARROS;
    }
}
