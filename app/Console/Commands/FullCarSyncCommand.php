<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class FullCarSyncCommand extends Command
{
    protected $signature = 'car:full-sync';

    public function handle()
    {
        $list = [

            // pickups
            //'toyota hilux',
            //'fiat toro',
            //'nissan frontier',
            //'chevrolet s10',
            //'mitsubishi l200',
            //'ford ranger',
            //'dodge ram',
            //'volkswagen amarok',

            // carros populares
            //'fiat argo',
            //'ford ka',
            //'chevrolet onix',
            'peugeot 208',
            //'volkswagen polo',
            //'volkswagen gol',
            //'volkswagen fox',
            //'hyundai hb20',

            //'audi a3',
            'audi a4',
            //'audi a5',
            'bmw 320i',
            //'bmw m2',
            //'bmw m3',
            'ford fusion',
            //'ford mustang',
            //'chevrolet camaro',
            //'chevrolet cruze',
            //'honda city',
            'honda civic',
            //'hyundai hb20s',
            'mercedes-benz c-180',
            'mitsubishi lancer',
            //'nissan 350z',
            'nissan 370z',
            //'nissan sentra',
            'toyota corolla',
            'volkswagen jetta',
            //'volkswagen virtus',
            //'renault fluence',
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
