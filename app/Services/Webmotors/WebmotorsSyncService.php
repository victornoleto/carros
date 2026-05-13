<?php

namespace App\Services\Webmotors;

use App\Enums\CarProviderEnum;
use App\Services\CarSyncService;

class WebmotorsSyncService extends CarSyncService
{
    public static function provider(): CarProviderEnum
    {
        return CarProviderEnum::WEBMOTORS;
    }

    public function getPageRequestUrl(string $brand, string $model, int $page = 1): string
    {
        return '/api/search/car';
    }

    public function getPageRequestOptions(string $brand, string $model, int $page = 1): array
    {
        $filter = config('car_scraping.state_filter');

        $filter = empty($filter) ? 'estoque' : $filter;

        $searchUrl = $this->provider->getUrl()."/carros/$filter/$brand/$model";

        $searchUrl .= '?'.http_build_query([
            'tipoveiculo' => 'carros',
            'marca1' => strtoupper($brand),
            'modelo1' => strtoupper($model),
            'page' => $page,
        ]);

        return [
            'query' => [
                'url' => $searchUrl,
                'displayPerPage' => 47,
                'actualPage' => $page,
                'showMenu' => 'true',
                'showCount' => 'true',
                'showBreadCrumb' => 'true',
                'order' => 1,
                'mediaZeroKm' => 'true',
            ],
            'headers' => $this->getPageRequestHeaders($brand, $model, $page),
        ];
    }

    public function getPageRequestHeaders(string $brand, string $model, int $page = 1): array
    {
        return [
            'accept' => 'application/json',
            'accept-language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
            'priority' => 'u=1, i',
            'sec-ch-ua' => '"Google Chrome";v="147", "Not.A/Brand";v="8", "Chromium";v="147"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Linux"',
            'sec-fetch-dest' => 'empty',
            'sec-fetch-mode' => 'cors',
            'sec-fetch-site' => 'same-origin',
            'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',
        ];
    }

    public function getPageUnprocessedResults(string $pageResults): array
    {
        $data = json_decode($pageResults, true);

        return $data['SearchResults'] ?? [];
    }
}
