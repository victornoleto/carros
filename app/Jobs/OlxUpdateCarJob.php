<?php

namespace App\Jobs;

use App\Models\OlxCar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class OlxUpdateCarJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Crawler $node;

    /**
     * Create a new job instance.
     */
    public function __construct(public OlxCar $car)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $contents = Http::get($this->car->url)->body();

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

        $updates = [];

        if (isset($characteristics['Modelo'])) {
            $updates['version'] = $characteristics['Modelo'];
        }

        if (isset($characteristics['Cor'])) {
            $updates['color'] = $characteristics['Cor'];
        }

        $this->car->update($updates);
    }
}
