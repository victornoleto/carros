<?php

namespace App\Jobs\Olx;

use App\Enums\CarProviderEnum;
use App\Jobs\CarSyncJob;
use App\Services\OlxService;

/**
 * Processar páginas de anúncios da OLX.
 *
 * @author Victor Noleto <victornoleto@sysout.com.br>
 * @since 11/07/2023
 * @version 1.0.0
 */
class OlxSyncJob extends CarSyncJob
{
    public function __construct(string $brand, string $model)
    {
        parent::__construct($brand, $model, CarProviderEnum::OLX, new OlxService());
    }
}
