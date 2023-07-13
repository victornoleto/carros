<?php

namespace App\Services\Olx;

use App\Enums\CarProviderEnum;
use App\Services\CarSyncService;
use Symfony\Component\DomCrawler\Crawler;

class OlxSyncService extends CarSyncService
{
    public function __construct()
    {
        parent::__construct([
            'base_uri' => 'https://www.olx.com.br',
        ]);
    }

    public function getProvider(): CarProviderEnum
    {
        return CarProviderEnum::OLX();
    }

    public function getPageResult(string $brand, string $model, int $page = 1): string
    {
        $url = $this->getPageUrl($brand, $model, $page);

        $response = $this->httpClient->request('get', $url);

        return $response->getBody()->getContents();
    }

    public function getAdResults($pageResult): array
    {
        if (!$this->checkPageHasAds($pageResult)) {
            return [];
        }

        $crawler = new Crawler($pageResult);

        $nodes = $crawler->filter('#ad-list > li:not(.sponsored)');

        $ads = [];

        foreach ($nodes as $node) {
            array_push($ads, (new Crawler($node))->html());
        }

        return $ads;
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

    private function getPageUrl(string $brand, string $model, int $page = 1): string
    {
        $brandsDict = [
            'volkswagen' => 'vw-volkswagen',
            'chevrolet' => 'gm-chevrolet',
        ];

        $brand = $brandsDict[$brand] ?? $brand;

        $url = "/autos-e-pecas/carros-vans-e-utilitarios/$brand/$model";

        $state = env('STATE_FILTER');

        if ($state) {
            $url .= "/estado-$state";
        }
        
        $url .= "?o=$page&sf=1";

        return $url;
    }
}
