<?php

namespace App\Jobs\Webmotors;

use App\Jobs\CarProcessJob;
use App\Models\Car;

class WebmotorsProcessJob extends CarProcessJob
{
    public function getAdData(): array
    {
        return [];
    }

    public function onCarSaved(Car $car): void
    {
    }
}
