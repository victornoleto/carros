<?php

namespace App\Services\Olx;

use App\Services\CarSyncService;
use App\Traits\CarSyncTrait;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Serviço para extrair os anúncios em uma página da Olx.
 *
 * @author Victor Noleto <victornoleto@sysout.com.br>
 * @since 12/07/2023 
 * @version 1.0.0
 */
class OlxSyncService extends CarSyncService
{
    use CarSyncTrait;

    private Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'base_uri' => 'https://www.olx.com.br',
            'verify' => false,
        ]);
    }

    public function getPageResult(string $brand, string $model, int $page = 1): string
    {
        $url = $this->getAdsPageUrl($brand, $model, $page);

        $response = $this->httpClient->request('get', $url);

        return $response->getBody()->getContents();
    }

    public function getAdResults(string $pageResult): array
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

    public function getAdData(string $brand, string $model, string $adResult): array
    {
        $service = new OlxProcessService($brand, $model, $adResult);

        $data = $service->process();

        return $data;
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

    private function getAdsPageUrl(string $brand, string $model, int $page = 1): string
    {
        $mutate = [
            'volkswagen' => 'vw-volkswagen',
            'chevrolet' => 'gm-chevrolet',
        ];

        $brand = $mutate[$brand] ?? $brand;

        $url = "/autos-e-pecas/carros-vans-e-utilitarios/$brand/$model/estado-go?o=$page&sf=1";

        return $url;
    }
}
