<?php

namespace App\Jobs\UsadosBr;

use App\Enums\CarProviderEnum;
use App\Jobs\CarProcessJob;

class UsadosBrProcessJob extends CarProcessJob
{
    public function getProvider(): CarProviderEnum
    {
        return CarProviderEnum::USADOSBR();
    }
}
