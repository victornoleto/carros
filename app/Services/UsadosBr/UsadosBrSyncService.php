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

    public function getPageResult(string $brand, string $model, int $page = 1): string
    {
        $url = $this->getPageUrl($brand, $model, $page);

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

    private function getPageUrl(string $brand, string $model, int $page = 1): string
    {
        $filter = env('STATE_FILTER', 'br');

        $url = "/carros/$filter/$brand/$model";
        
        $url .= "?page=$page";

        return $url;
    }

    public static function getHeaders()
    {
        return [
            'authority' => 'www.usadosbr.com',
            'accept' => 'application/json, text/plain, */*',
            'cookie' => '_pxvid=df5df964-f05d-11ed-9243-456566786f75; at_check=true; AMCVS_3ADD33055666F1A47F000101%40AdobeOrg=1; AMCV_3ADD33055666F1A47F000101%40AdobeOrg=179643557%7CMCIDTS%7C19545%7CMCMID%7C42967538520206986612327726162499588296%7CMCOPTOUT-1688688011s%7CNONE%7CvVersion%7C5.5.0; mbox=PC#862090299e0340ddbec9951e85e9a3e0.34_0#1751925612|session#31d53e8d2c564795b3853d44417d5ffb#1688682672; WebMotorsVisitor=1; WMLastFilterSearch=%7B%22car%22%3A%22carros%2Festoque%2Fhonda%2Fcivic%3Festadocidade%3Destoque%26marca1%3DHONDA%26modelo1%3DCIVIC%26autocomplete%3Dcivi%26autocompleteTerm%3DHONDA%2520CIVIC%26lkid%3D1705%22%2C%22bike%22%3A%22motos%2Festoque%22%2C%22estadocidade%22%3A%22estoque%22%2C%22lastType%22%3A%22car%22%2C%22cookie%22%3A%22v3%22%2C%22ano%22%3A%7B%7D%2C%22preco%22%3A%7B%7D%2C%22marca%22%3A%22HONDA%22%2C%22modelo%22%3A%22CIVIC%22%7D; WebMotorsLastSearches=%5B%7B%22route%22%3A%22carros%2Festoque%2Fhonda%2Fcivic%22%2C%22query%22%3A%22%3Festadocidade%3Destoque%26marca1%3DHONDA%26modelo1%3DCIVIC%26autocompleteTerm%3DHONDA%2520CIVIC%26lkid%3D1705%22%7D%2C%7B%22route%22%3A%22carros%2Fgo-goiania%2Ftoyota%2Fcorolla%22%2C%22query%22%3A%22%3Festadocidade%3DGoi%25C3%25A1s%2520-%2520Goi%25C3%25A2nia%26marca1%3DTOYOTA%26modelo1%3DCOROLLA%26autocompleteTerm%3DTOYOTA%20COROLLA%22%7D%2C%7B%22route%22%3A%22carros%2Fgo-goiania%2Faudi%2Fa3%22%2C%22query%22%3A%22%3Festadocidade%3DGoi%25C3%25A1s%2520-%2520Goi%25C3%25A2nia%26marca1%3DAUDI%26modelo1%3DA3%26autocompleteTerm%3DAUDI%20A3%22%7D%2C%7B%22route%22%3A%22carros%2Festoque%2Faudi%2Fa3%22%2C%22query%22%3A%22%3Festadocidade%3Destoque%26marca1%3DAUDI%26modelo1%3DA3%26autocompleteTerm%3DAUDI%20A3%22%7D%2C%7B%22route%22%3A%22carros%2Festoque%2Fporsche%2F911%22%2C%22query%22%3A%22%3Festadocidade%3Destoque%26marca1%3DPORSCHE%26modelo1%3D911%26autocompleteTerm%3DPORSCHE%20911%22%7D%2C%7B%22route%22%3A%22carros%2Festoque%2Fporsche%2Fmacan%22%2C%22query%22%3A%22%3Festadocidade%3Destoque%26marca1%3DPORSCHE%26modelo1%3DMACAN%26autocompleteTerm%3DPORSCHE%20MACAN%22%7D%2C%7B%22route%22%3A%22carros%2Festoque%2Fporsche%2Fcayenne%22%2C%22query%22%3A%22%3Festadocidade%3Destoque%26marca1%3DPORSCHE%26modelo1%3DCAYENNE%26autocompleteTerm%3DPORSCHE%20CAYENNE%22%7D%2C%7B%22route%22%3A%22carros%2Festoque%2Fland%20rover%2Frange%20rover%20evoque%22%2C%22query%22%3A%22%3Festadocidade%3Destoque%26marca1%3DLAND%2520ROVER%26modelo1%3DRANGE%2520ROVER%2520EVOQUE%26autocompleteTerm%3DLAND%2520ROVER%2520RANGE%2520ROVER%2520EVOQUE%26lkid%3D1705%22%7D%2C%7B%22route%22%3A%22carros%2Festoque%2Ffiat%2F147%22%2C%22query%22%3A%22%3Festadocidade%3Destoque%26marca1%3DFIAT%26modelo1%3D147%26autocompleteTerm%3DFIAT%20147%22%7D%2C%7B%22route%22%3A%22carros%2Festoque%2Fchevrolet%2Fchevette%22%2C%22query%22%3A%22%3Festadocidade%3Destoque%26marca1%3DCHEVROLET%26modelo1%3DCHEVETTE%26autocompleteTerm%3DCHEVROLET%20CHEVETTE%22%7D%5D; WebMotorsSearchDataLayer=%7B%22search%22%3A%7B%22location%22%3A%7B%7D%2C%22ordination%22%3A%7B%22name%22%3A%22Mais%20relevantes%22%2C%22id%22%3A1%7D%2C%22pageNumber%22%3A1%2C%22totalResults%22%3A5024%2C%22vehicle%22%3A%7B%22type%22%3A%7B%22id%22%3A1%2C%22name%22%3A%22carro%22%7D%7D%2C%22cardExhibition%22%3A%7B%22id%22%3A%221%22%2C%22name%22%3A%22Cards%20Grid%22%7D%2C%22eventType%22%3A%22buscaRealizada%22%7D%7D; WebMotorsTrackingFrom=ordenacaoRealizada',
            'sec-ch-ua' => '"Not.A/Brand";v="8", "Chromium";v="114", "Google Chrome";v="114"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Linux"',
            'sec-fetch-dest' => 'empty',
            'sec-fetch-mode' => 'cors',
            'sec-fetch-site' => 'same-origin',
            'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36',
        ];
    }
}