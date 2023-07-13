<?php

namespace App\Services;

use App\Enums\CarProviderEnum;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

abstract class CarSyncService
{

    public Client $httpClient;

    public function __construct(array $httpClientOptions = [])
    {
        # proxies
        $proxies = [
            'http'  => 'http://201.95.254.137',
            'https' => 'https://177.86.120.11',
        ];

        $httpClientOptions = array_merge([
            //RequestOptions::PROXY => $proxies,
            RequestOptions::VERIFY => false,
            RequestOptions::TIMEOUT => 60
        ], $httpClientOptions);

        $this->httpClient = new Client($httpClientOptions);
    }

    abstract public function getProvider(): CarProviderEnum;

    abstract public function getPageResult(string $brand, string $model, int $page = 1): string|array;

    abstract public function getAdResults($pageResult): array;

    public function getResults(string $brand, string $models, int $page = 1): array
    {
        $pageResult = $this->getPageResult($brand, $models, $page);

        $adResults = $this->getAdResults($pageResult);

        $result = [];

        foreach ($adResults as $adResult) {

            $row = [
                'data' => $adResult
            ];

            try {

                $data = $this->getAdData($brand, $models, $adResult);

                $row['car'] = $data;
                
            } catch (\Exception $e) {
                $row['error'] = $e->getMessage();
            }

            $row['status'] = isset($row['car']);

            array_push($result, $row);
        }

        return $result;
    }

    public function getAdData(string $brand, string $model, $adResult): array
    {
        $provider = $this->getProvider();

        $serviceClass = $provider->getProcessServiceClass();

        $service = app($serviceClass, [
            'brand' => $brand,
            'model' => $model,
            'adResult' => $adResult
        ]);

        $data = $service->getData();

        return $data;
    }
}
