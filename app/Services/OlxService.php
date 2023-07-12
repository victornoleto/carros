<?php

namespace App\Services;

use App\Jobs\Olx\OlxProcessPageJob;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class OlxService
{
    private Client $httpClient;

    public function __construct()
    {

        $this->httpClient = new Client([
            'base_uri' => 'https://www.olx.com.br',
            'verify' => false,
        ]);
    }
    
    public function sync(string $brand, string $model, int $page = 1)
    {
        Log::debug("[olx][sync][$brand][$model] Starting page #$page sync...");

        $response = $this->httpClient->request(
            'GET',
            $this->getUrl($brand, $model, $page)
        );

        $contents = $response->getBody()->getContents();

        if ($this->checkPageHasAds($contents)) {

            Log::debug('[olx]['.$brand.']['.$model.'] Dispatching process job: page #'.$page);

            OlxProcessPageJob::dispatch($brand, $model, $page, $contents)
                ->onQueue('olx:sync');
        
            $this->sync($brand, $model, $page + 1);
        }
    }

    private function checkPageHasAds(string $contents): bool
    {

        $crawler = new Crawler($contents);

        $dom1 = $crawler->filter('[id="listing-no-result"]');

        $dom2 = $crawler->filter('.recommendation');

        $dom3 = $crawler->filter('.states-title');

        $count = $dom1->count() + $dom2->count() + $dom3->count();

        return $count === 0;
    }

    private function getUrl(string $brand, string $model, int $page = 1): string
    {

        $mutate = [
            'volkswagen' => 'vw-volkswagen',
            'chevrolet' => 'gm-chevrolet',
        ];

        $brand = $mutate[$brand] ?? $brand;

        $url = "/autos-e-pecas/carros-vans-e-utilitarios/$brand/$model?o=$page&sf=1";

        return $url;
    }
}
