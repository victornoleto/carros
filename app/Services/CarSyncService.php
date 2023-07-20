<?php

namespace App\Services;

use App\Enums\CarProviderEnum;
use App\Traits\CarProviderTrait;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

abstract class CarSyncService
{
    use CarProviderTrait;

    public Client $httpClient;

    public CarProviderEnum $provider;

    public function __construct()
    {
        $this->httpClient = new Client([
            RequestOptions::VERIFY => false,
            RequestOptions::TIMEOUT => 60
        ]);

        $this->setProviderByClassName();
    }

    abstract public function getPageRequestUrl(string $brand, string $model, int $page = 1): string;

    abstract public function getPageUnprocessedResults(string $pageResults): array;

    public function getPageEntireResults(string $brand, string $model, int $page = 1): string|array
    {
        $url = $this->getPageRequestUrl($brand, $model, $page);

        $fullUrl = $this->provider->getUrl() . $url;

        $options = $this->getPageRequestOptions($brand, $model, $page);

        $response = $this->httpClient->request('get', $fullUrl, $options);

        $contents = $response->getBody()->getContents();

        return $contents;
    }

    public function getPageRequestOptions(string $brand, string $model, int $page = 1): array
    {
        return [
            'headers' => $this->getPageRequestHeaders($brand, $model, $page)
        ];
    }

    public function getPageRequestHeaders(string $brand, string $model, int $page = 1): array
    {
        return [
            'authority' => str_replace('https://', '', $this->provider->getUrl()),
            'accept' => 'application/json, text/plain, */*',
            'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36',
        ];
    }

    public function getPageResults(string $brand, string $model, int $page = 1): array
    {
        $pageResults = $this->getPageEntireResults($brand, $model, $page);

        $unprocessedResults = $this->getPageUnprocessedResults($pageResults);
        
        $results = [];
        
        foreach ($unprocessedResults as $unprocessedResult) {
            
            $row = [
                'unprocessedResult' => $unprocessedResult
            ];
            
            try {
                
                $processService = $this->provider->getProcessService([
                    'brand' => $brand,
                    'model' => $model,
                    'data' => $unprocessedResult,
                ]);
        
                $row['result'] = $processService->getData();
                
            } catch (\Exception $e) {

                $row['error'] = $e->getMessage();
            }

            array_push($results, $row);
        }

        return $results;
    }
}
