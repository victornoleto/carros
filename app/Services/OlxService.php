<?php

namespace App\Services;

use App\Jobs\OlxProcessPageJob;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler; 

class OlxService {

    private Client $httpClient;

    public function __construct() {

        $this->httpClient = new Client([
            'base_uri' => 'https://www.olx.com.br',
            'verify' => false,
        ]);
    }
    
    public function sync(string $brand, string $model, int $page = 1) {

        $url = "/autos-e-pecas/carros-vans-e-utilitarios/$brand/$model?o=$page&sf=1";

        $response = $this->httpClient->request('GET', $url);

        $contents = $response->getBody()->getContents();

        if ($this->checkPageHasAds($contents)) {

            Log::debug('[OlxService]['.$brand.']['.$model.']['.$page.'] Dispatching job...');

            $job = new OlxProcessPageJob($brand, $model, $page, $contents);
        
            dispatch($job)->onQueue('olx-process-page');

            $this->sync($brand, $model, $page + 1);
        }
    }

    private function checkPageHasAds(string $contents) {

        $crawler = new Crawler($contents);

        $dom1 = $crawler->filter('[id="listing-no-result"]');

        $dom2 = $crawler->filter('.recommendation');

        $dom3 = $crawler->filter('.states-title');

        $count = $dom1->count() + $dom2->count() + $dom3->count();

        return $count === 0;
    }
}