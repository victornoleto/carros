<?php

namespace App\Jobs\Webmotors;

use App\Enums\CarProviderEnum;
use App\Jobs\CarProcessJob;
use App\Models\Car;

class WebmotorsProcessJob extends CarProcessJob
{
    public function getProvider(): CarProviderEnum
    {
        return CarProviderEnum::WEBMOTORS();
    }
}
