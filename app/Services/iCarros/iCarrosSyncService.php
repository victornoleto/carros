<?php

namespace App\Services\iCarros;

use App\Services\CarSyncService;
use App\Services\iCarros\iCarrosProcessService;
use App\Traits\CarSyncTrait;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class iCarrosSyncService extends CarSyncService {

	public static $url = "https://www.icarros.com.br";

	use CarSyncTrait;

    private Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'verify' => false,
        ]);
    }

    public function getPageResult(string $brand, string $model, int $page = 1): string
    {
        $url = $this->getPageUrl($brand, $model, $page);

        $response = $this->httpClient->request('get', $url);

        return $response->getBody()->getContents();
    }

    public function getAdResults(string $pageResult): array
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

    public function getAdData(string $brand, string $model, string $adResult): array
    {
        $service = new iCarrosProcessService($brand, $model, $adResult);

        $data = $service->process();

        return $data;
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
		$url = self::$url."/comprar/$brand/$model?pag=$page";

		return $url;
	}
}