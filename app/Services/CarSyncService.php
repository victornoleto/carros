<?php

namespace App\Services;

use App\Enums\CarProviderEnum;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;

abstract class CarSyncService
{
    public Client $httpClient;

    public CarProviderEnum $provider;

    public function __construct()
    {
        $this->httpClient = new Client([
            RequestOptions::VERIFY => config('car_scraping.verify_tls', true),
            RequestOptions::TIMEOUT => config('car_scraping.timeout', 60),
        ]);

        $this->provider = static::provider();
    }

    abstract public static function provider(): CarProviderEnum;

    abstract public function getPageRequestUrl(string $brand, string $model, int $page = 1): string;

    abstract public function getPageUnprocessedResults(string $pageResults): array;

    public function getPageEntireResults(string $brand, string $model, int $page = 1): string|array
    {
        $url = $this->getPageRequestUrl($brand, $model, $page);

        $fullUrl = $this->provider->getUrl().$url;

        $options = $this->getPageRequestOptions($brand, $model, $page);

        if ($this->shouldUseProxy()) {
            return $this->getPageEntireResultsViaProxy($fullUrl, $options);
        }

        try {
            $response = $this->httpClient->request('get', $fullUrl, $options);
        } catch (RequestException $e) {
            if ($e->getResponse()?->getStatusCode() !== 403) {
                throw $e;
            }

            return $this->getPageEntireResultsViaProxy($fullUrl, $options);
        }

        $contents = $response->getBody()->getContents();

        return $contents;
    }

    protected function getPageEntireResultsViaProxy(string $fullUrl, array $options): string
    {
        $proxyUrl = config('car_scraping.proxy_url');

        $proxyToken = config('car_scraping.proxy_token');

        if (! $proxyUrl || ! $proxyToken) {
            throw new \RuntimeException('Request blocked with 403 and CAR_SCRAPING_PROXY_URL/CAR_SCRAPING_PROXY_TOKEN are not configured');
        }

        $url = $this->buildUrlWithQuery($fullUrl, $options['query'] ?? []);

        $response = $this->httpClient->request('post', $proxyUrl, [
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'x-proxy-token' => $proxyToken,
            ],
            'json' => [
                'url' => $url,
                'headers' => $options['headers'] ?? [],
                'timeout_seconds' => config('car_scraping.timeout', 60),
            ],
        ]);

        $payload = json_decode($response->getBody()->getContents(), true);

        if (($payload['status_code'] ?? 0) >= 400) {
            throw new \RuntimeException('Proxy upstream returned HTTP '.$payload['status_code']);
        }

        $body = $payload['body'] ?? '';

        return is_string($body) ? $body : json_encode($body, JSON_THROW_ON_ERROR);
    }

    private function shouldUseProxy(): bool
    {
        $providers = config('car_scraping.proxy_providers', []);

        return in_array($this->provider->value, $providers, true);
    }

    private function buildUrlWithQuery(string $url, array $query): string
    {
        if (empty($query)) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.http_build_query($query);
    }

    public function getPageRequestOptions(string $brand, string $model, int $page = 1): array
    {
        return [
            'headers' => $this->getPageRequestHeaders($brand, $model, $page),
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
                'unprocessedResult' => $unprocessedResult,
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
