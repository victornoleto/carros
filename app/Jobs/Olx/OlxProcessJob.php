<?php

namespace App\Jobs\Olx;

use App\Enums\CarProviderEnum;
use App\Jobs\CarProcessJob;

class OlxProcessJob extends CarProcessJob
{
    public function getProvider(): CarProviderEnum
    {
        return CarProviderEnum::OLX();
    }
}
