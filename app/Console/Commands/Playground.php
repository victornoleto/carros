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
            //'bmw 320i',
            //'audi a3',
            //'audi a4',
            //'mitsubishi lancer',
            //'vw-volkswagen jetta',
            //'honda civic',
            //'toyota corolla',
            //'mercedes-benz c-180',
            //'honda city',
            //'vw-volkswagen virtus',
            //'vw-volkswagen polo',
            //'peugeot 208',
            //'ford mustang',
            //'nissan sentra',
            //'ford fusion',
            //'hyundai hb20',
            //'hyundai hb20s',
            //'gm-chevrolet camaro',
            //'gm-chevrolet onix',
            //'vw-volkswagen gol',
            //'fiat argo',
            //'audi a5',
            'nissan 370z',
            'nissan 350z'
        ];

        foreach ($list as $item) {

            list($brand, $model) = explode(' ', $item);

            Artisan::call('olx:sync', [
                'brand' => $brand,
                'model' => $model
            ]);
        }
    }
}
