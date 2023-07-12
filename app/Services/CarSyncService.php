<?php

namespace App\Services;

use App\Enums\CarProviderEnum;
use GuzzleHttp\Client;

abstract class CarSyncService {

	public Client $httpClient;

	public function __construct(array $httpClientOptions = [])
	{
		$httpClientOptions = array_merge([
			'verify' => false,
			'timeout' => 10
		], $httpClientOptions);

		$this->httpClient = new Client($httpClientOptions);
	}

	abstract function getProvider(): CarProviderEnum;

	abstract function getPageResult(string $brand, string $model, int $page = 1): string|array;

	abstract function getAdResults($pageResult): array;

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

	public function getAdData(string $brand, string $model, string $adResult): array
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