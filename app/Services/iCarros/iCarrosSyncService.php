<?php

namespace App\Services\iCarros;

use App\Services\CarSyncService;
use Symfony\Component\DomCrawler\Crawler;

class iCarrosSyncService extends CarSyncService
{
    public function getPageRequestUrl(string $brand, string $model, int $page = 1): string
    {
        $url = '/comprar';

        if (env('STATE_FILTER')) {
            $url .= '/' . env('STATE_FILTER');
        }
        
        $url .= "/$brand/$model?pag=$page";

        return $url;
    }

    public function getPageUnprocessedResults(string $pageResults): array
    {
        $crawler = new Crawler($pageResults);

        $nodes = $crawler->filter('.offer-card');

        $ads = [];

        foreach ($nodes as $node) {
            array_push($ads, (new Crawler($node))->html());
        }

        return $ads;
    }
}
