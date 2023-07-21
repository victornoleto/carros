<?php

namespace App\Services\UsadosBr;

use App\Services\CarProcessService;
use Illuminate\Support\Carbon;
use Symfony\Component\DomCrawler\Crawler;

class UsadosBrProcessService extends CarProcessService
{
    public Crawler $node;

    public function __construct(
        string $brand,
        string $model,
        public string $data,
    ) {
        parent::__construct($brand, $model);
    }

    public function getData(): array
    {
        $this->node = new Crawler($this->data);

        return parent::getData();
    }

    public function getVersion(): string|null
    {
        $text = $this->node->filter('.css-kufh1x')->text();

        return $text;
    }

    public function getYear(): int
    {
        return $this->getYearAndYearModel()[0];
    }

    public function getYearModel(): int|null
    {
        return $this->getYearAndYearModel()[0];
    }

    public function getPrice(): float
    {
        $text = $this->node->filter('.css-cn4x9d')->text();

        $text = str_replace('R$', '', $text);

        $text = str_replace('.', '', $text);

        $text = str_replace(',', '.', $text);

        $price = floatval($text);

        return $price;
    }

    public function getOdometer(): int
    {
        $text = $this->node->filter('.css-ljtdvh')
            ->children()
                ->eq(1)->text();

        $text = str_replace(' KM', '', $text);
        
        $text = str_replace('.', '', $text);

        $odometer = intval($text);

        return $odometer;
    }

    public function getState(): string
    {
        return $this->getStateAndCity()[1];
    }

    public function getCity(): string
    {
        return $this->getStateAndCity()[0];
    }

    public function getProviderId(): string
    {
        return md5($this->getProviderUrl());
    }

    public function getProviderUpdatedAt(): Carbon
    {
        return Carbon::now();
    }

    public function getProviderUrl(): string
    {
        $href = $this->node->filter('a')->attr('href');

        return $href;
    }

    private function getStateAndCity(): array
    {
        $text = $this->node->filter('.css-0 .css-30myhr:first-child')->text();

        list($city, $state) = explode(' - ', $text);

        $parts = [$city, $state];

        return $parts;
    }

    private function getYearAndYearModel(): array
    {

        $text = $this->node->filter('.css-ljtdvh')
            ->children()
                ->eq(0)->text();

        list($year, $yearModel) = explode('/', $text);

        return [
            intval($year),
            intval($yearModel)
        ];
    }
}
