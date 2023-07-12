<?php

namespace App\Services\iCarros;

use App\Enums\CarProviderEnum;
use App\Services\CarSyncService;
use Symfony\Component\DomCrawler\Crawler;

class iCarrosSyncService extends CarSyncService {

	public static $serverUrl = 'https://www.icarros.com.br';

    public function __construct()
    {
        parent::__construct();
    }

    public function getProvider(): CarProviderEnum
    {
        return CarProviderEnum::ICARROS();
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

        $nodes = $crawler->filter('.offer-card');

        $ads = [];

        foreach ($nodes as $node) {

			$html = $node->ownerDocument->saveHTML($node);

			$html = str_replace("\n", "", $html);

            array_push($ads, $html);
        }

        return $ads;
    }

    private function checkPageHasAds(string $contents): bool
    {
        $crawler = new Crawler($contents);

        $offers = $crawler->filter('#cards-grid')
			->children();

        return $offers->count() > 0;
    }

	private function getPageUrl(string $brand, string $model, int $page = 1): string
	{
		$url = self::$serverUrl."/comprar/$brand/$model?pag=$page";

		return $url;
	}
}