<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class Playground extends Command
{
    protected $signature = 'play';

    public function handle()
    {
        $list = [
            'audi a3',
            'audi a4',
            'audi a5',
            'fiat argo',
            'ford fusion',
            'ford ka',
            'ford mustang',
            'gm-chevrolet camaro',
            'gm-chevrolet onix',
            'honda city',
            'honda civic',
            'hyundai hb20',
            'hyundai hb20s',
            'mercedes-benz c-180',
            'mitsubishi lancer',
            'nissan 350z',
            'nissan 370z',
            'nissan sentra',
            'peugeot 208',
            'toyota corolla',
            'volkswagen gol',
            'volkswagen jetta',
            'volkswagen polo',
            'volkswagen virtus',
        ];

        foreach ($list as $item) {

            list($brand, $model) = explode(' ', $item);

            Artisan::call('car:sync', [
                'brand' => $brand,
                'model' => $model
            ]);
        }
    }
}
