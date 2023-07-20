<?php

namespace App\Services\Olx;

use App\Services\CarSyncService;
use Symfony\Component\DomCrawler\Crawler;

class OlxSyncService extends CarSyncService
{
    public function getPageRequestUrl(string $brand, string $model, int $page = 1): string
    {
        $brandsDict = [
            'volkswagen' => 'vw-volkswagen',
            'chevrolet' => 'gm-chevrolet',
        ];

        $brand = $brandsDict[$brand] ?? $brand;

        $url = "/autos-e-pecas/carros-vans-e-utilitarios/$brand/$model";

        $url .= "?o=$page&sf=1";

        return $url;
    }

    public function getPageUnprocessedResults(string $pageResults): array
    {
        $crawler = new Crawler($pageResults);

        $nodes = $crawler->filter('#ad-list > li:not(.sponsored)');

        $ads = [];

        foreach ($nodes as $node) {
            array_push($ads, (new Crawler($node))->html());
        }

        return $ads;
    }
}
