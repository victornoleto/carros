<?php

namespace App\Jobs\iCarros;

use App\Enums\CarProviderEnum;
use App\Jobs\CarProcessJob;
use App\Models\Car;

class iCarrosProcessJob extends CarProcessJob
{
    public function getProvider(): CarProviderEnum
    {
        return CarProviderEnum::ICARROS();
    }
}
