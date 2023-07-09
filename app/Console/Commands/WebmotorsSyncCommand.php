<?php

namespace App\Console\Commands;

use App\Services\WebmotorsService;
use Illuminate\Console\Command;

class WebmotorsSyncCommand extends Command
{
    protected $signature = 'app:webmotors-sync-command {brand} {model} {page=1}';

    public function handle()
    {
        $brand = $this->argument('brand');
        $model = $this->argument('model');
        $page = $this->argument('page');

        $service = new WebmotorsService();

        $service->sync($brand, $model, $page);
    }
}
