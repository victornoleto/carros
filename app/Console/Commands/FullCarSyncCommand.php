<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class FullCarSyncCommand extends Command
{
    protected $signature = 'cars:full-sync';

    public function handle()
    {
        $list = [

            // pickups
            'toyota hilux',
            'fiat toro',
            'nissan frontier',
            'chevrolet s10',
            'mitsubishi l200',
            'ford ranger',
            'dodge ram',
            'volkswagen amarok',

            //carros "populares"
            'fiat argo',
            'ford ka',
            'chevrolet onix',
            'peugeot 208',
            'volkswagen polo',
            'volkswagen gol',
            'volkswagen fox',
            'hyundai hb20',
            'honda city',

            // outros
            'nissan sentra',
            'volkswagen virtus',
            'renault fluence',
            'mitsubishi lancer',
            'hyundai hb20s',
            'chevrolet cruze',
            'honda civic',
            'toyota corolla',
            'ford fusion',
            'audi a3',
            'audi a4',
            'audi a5',
            'bmw 320i',
            'bmw m2',
            'bmw m3',
            'ford mustang',
            'chevrolet camaro',
            'mercedes-benz c-180',
            'nissan 350z',
            'nissan 370z',
            'volkswagen jetta',
        ];

        foreach ($list as $item) {

            list($brand, $model) = explode(' ', $item);

            Artisan::call('cars:sync', [
                'brand' => $brand,
                'model' => $model,
                //'provider' => 'olx'
            ]);
        }
    }
}
