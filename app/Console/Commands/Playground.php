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
            'honda civic',
            'toyota corolla',
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
