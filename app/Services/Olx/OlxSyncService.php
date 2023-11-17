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

        if (env('STATE_FILTER')) {
            $url .= '/estado-' . env('STATE_FILTER');
        }

        // ano >= 2000
        // quilometragem <= 300000
        $url .= "?o=$page&sf=1&me=300000&rs=50";

        return $url;
    }

    public function getPageUnprocessedResults(string $pageResults): array
    {
        $crawler = new Crawler($pageResults);

        $nodes = $crawler->filter('script#__NEXT_DATA__');

        $json = $nodes->innerText();

        $data = json_decode($json, true);

        $ads = $data['props']['pageProps']['ads'];

        $ads = array_filter($ads, function($item) {
            return !isset($item['advertisingId']);
        });

        /* foreach ($nodes as $node) {
            array_push($ads, (new Crawler($node))->html());
        } */

        return $ads;
    }
}
