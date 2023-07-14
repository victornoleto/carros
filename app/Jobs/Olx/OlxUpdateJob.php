<?php

namespace App\Jobs\Olx;

use App\Models\Car;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class OlxUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Crawler $node;

    public function __construct(public Car $car)
    {
    }

    public function handle(): void
    {
        $contents = Http::get($this->car->provider_url)->body();

        $this->node = new Crawler($contents);

        $textElements = $this->node->filter('[data-ds-component="DS-Text"]');

        $characteristics = [];

        $characteristicsLabels = [
            'Categoria',
            'Modelo',
            'Marca',
            'Tipo de veículo',
            'Ano',
            'Quilometragem',
            'Potência do motor',
            'Combustível',
            'Câmbio',
            'Cor',
            'Portas',
            'Final de placa',
            'Tipo de direção',
        ];

        foreach ($textElements as $textElement) {

            $label = $textElement->nodeValue;

            if (in_array($label, $characteristicsLabels)) {
                $characteristics[$label] = $textElement->nextSibling?->nodeValue;
            }
        }

        if (isset($characteristics['Modelo'])) {

            $version = $characteristics['Modelo'];

            $version = str_replace("$this->car->brand $this->car->model ", "", $version);

            $this->car->update([
                'version' => $version
            ]);
    
            Log::debug('[car-update]['.$this->car->provider.']['.$this->car->brand.']['.$this->car->model.'] Version updated: '.$version);
        }
    }
}
