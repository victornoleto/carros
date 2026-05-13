<?php

namespace App\Jobs\UsadosBr;

use App\Enums\CarProviderEnum;
use App\Jobs\CarProcessJob;

class UsadosBrProcessJob extends CarProcessJob
{
    public static function provider(): CarProviderEnum
    {
        return CarProviderEnum::USADOSBR;
    }
}
