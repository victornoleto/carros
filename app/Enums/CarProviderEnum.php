<?php declare(strict_types=1);

namespace App\Enums;

use App\Services\iCarrosService;
use App\Services\OlxService;
use App\Services\WebmotorsService;
use BenSampo\Enum\Enum;

final class CarProviderEnum extends Enum
{
    const OLX = 'olx';

    const WEBMOTORS = 'webmotors';

    const ICARROS = 'icarros';

    public function getService() {

        switch ($this->value) {
            
            case self::OLX:
                return new OlxService();
            
            case self::WEBMOTORS:
                return new WebmotorsService();
            
            case self::ICARROS:
                return new iCarrosService();
        }
    }
}
