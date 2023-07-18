<?php

namespace App\Services\UsadosBr;

use App\Enums\CarProviderEnum;
use App\Services\CarSyncService;
use Symfony\Component\DomCrawler\Crawler;

class UsadosBrSyncService extends CarSyncService
{
    public static $serverUrl = 'https://www.usadosbr.com';

    public function __construct()
    {
        parent::__construct([
            'base_uri' => self::$serverUrl,
        ]);
    }

    public function getProvider(): CarProviderEnum
    {
        return CarProviderEnum::USADOSBR();
    }

    public function getPageResult(string $brand, string $model, int $page = 1, string|null $state = null): string
    {
        $url = $this->getPageUrl($brand, $model, $page, $state);

        $response = $this->httpClient->get($url, [
            'headers' => self::getHeaders(),
        ]);

        $contents = $response->getBody()->getContents();

        return $contents;
    }

    public function getAdResults($pageResult): array
    {
        $crawler = new Crawler($pageResult);

        $nodes = $crawler->filter('.css-g44pox, .css-1oxh7sl');

        $ads = [];

        foreach ($nodes as $node) {
            array_push($ads, (new Crawler($node))->html());
        }

        return $ads;
    }

    private function getPageUrl(string $brand, string $model, int $page = 1, string|null $state = null): string
    {
        $state = $state ?? 'br';

        $url = "/carros/$state/$brand/$model";

        $url .= "?page=$page";

        return $url;
    }

    public static function getHeaders()
    {
        return [
            'authority' => 'www.usadosbr.com',
            'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'accept-language' => 'pt-PT,pt;q=0.9,en-US;q=0.8,en;q=0.7',
            'cache-control' => 'max-age=0',
            'cookie' => 'utm_source=google; utm_today=1; laravel_session=eyJpdiI6ImltOUlWZlk1RElIXC84MWNFdVpUNVdRPT0iLCJ2YWx1ZSI6ImNUbWE1dDZ3VU9XNHJYQ2p6Qzdyb3hlMzFrdlM3RDhRRTM1ZlRleHR5d2ZvWDhncVZmQ0NkdjNmZkpRRDBXTktxQk8yZExEaE9iZldKYlYrOGJ0TW15cTQwNzIxQkk5ZVI1ZjNaM2hXZG92SFd2MHhDVjJ4VWRJdXdPWk42dE16IiwibWFjIjoiMmQ3N2Y0YjMzYjE3MjU5NTBkYWQwNGYzNGFjNDM0NDdkN2JkMDkyOGY2ZDU0NzQ0ZjUxZmQwZDI3ZGViZTdjZSJ9; _dd_s=rum=0&expire=1689464082913',
            'sec-ch-ua' => '"Not.A/Brand";v="8", "Chromium";v="114", "Google Chrome";v="114"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Linux"',
            'sec-fetch-dest' => 'document',
            'sec-fetch-mode' => 'navigate',
            'sec-fetch-site' => 'same-origin',
            'sec-fetch-user' => '?1',
            'upgrade-insecure-requests' => '1',
            'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36',
        ];
    }
}
