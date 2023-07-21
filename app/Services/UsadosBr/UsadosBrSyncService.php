<?php

namespace App\Services\UsadosBr;

use App\Services\CarSyncService;
use Symfony\Component\DomCrawler\Crawler;

class UsadosBrSyncService extends CarSyncService
{
    public function getPageUnprocessedResults(string $pageResults): array
    {
        $crawler = new Crawler($pageResults);

        $nodes = $crawler->filter('.css-g44pox, .css-1oxh7sl');

        $ads = [];

        foreach ($nodes as $node) {
            array_push($ads, (new Crawler($node))->html());
        }

        return $ads;
    }

    public function getPageRequestUrl(string $brand, string $model, int $page = 1): string
    {
        $filter = env('STATE_FILTER', 'br');

        $url = "/carros/$filter/$brand/$model";

        $url .= "?page=$page";

        return $url;
    }
}
