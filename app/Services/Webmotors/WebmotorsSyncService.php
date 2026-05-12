<?php

namespace App\Services\Webmotors;

use App\Services\CarSyncService;

class WebmotorsSyncService extends CarSyncService
{
    public function getPageRequestUrl(string $brand, string $model, int $page = 1): string
    {
        return '/api/search/car';
    }

    public function getPageRequestOptions(string $brand, string $model, int $page = 1): array
    {
        $filter = config('car_scraping.state_filter');

        $filter = empty($filter) ? 'estoque' : $filter;

        return [
            'query' => [
                'url' => $this->provider->getUrl()."/carros/$filter/$brand/$model",
                'actualPage' => $page,
            ],
            'headers' => $this->getPageRequestHeaders($brand, $model, $page),
        ];
    }

    public function getPageUnprocessedResults(string $pageResults): array
    {
        $data = json_decode($pageResults, true);

        return $data['NewSearchResults'] ?? [];
    }
}
