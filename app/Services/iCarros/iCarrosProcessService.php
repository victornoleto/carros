<?php

namespace App\Services\iCarros;

use App\Exceptions\CarProcessIgnoreException;
use App\Services\CarProcessService;
use Illuminate\Support\Carbon;
use Symfony\Component\DomCrawler\Crawler;

class iCarrosProcessService extends CarProcessService
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
        $version = $this->node->filter('.offer-card__header a')
            ->children()
                ->eq(1)->text();

        return $version;
    }

    public function getYear(): int
    {
        return $this->getYearAndYearModel()[0];
    }

    public function getYearModel(): int|null
    {
        return $this->getYearAndYearModel()[1];
    }

    public function getPrice(): float
    {
        $price = $this->node->filter('.offer-card__price-container')
            ->children()
                ->eq(0)->text();

        $price = str_replace('R$ ', '', $price);

        $price = str_replace('.', '', $price);

        $price = str_replace(',', '.', $price);

        $price = intval($price);

        return $price;
    }

    public function getOdometer(): int
    {
        $text = $this->node->filter('.info-container__car-info')
            ->children()
                ->eq(1)->text();

        $text = str_replace(' Km', '', $text);
        
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
        $url = explode('?', $this->getProviderUrl())[0];

        $parts = explode('/', $url);

        $id = str_replace('d', '', end($parts));

        return $id;
    }

    public function getProviderUpdatedAt(): Carbon
    {
        return Carbon::now();
    }

    public function getProviderUrl(): string
    {
        $url = $this->node->filter('.offer-card__image-container')
            ->attr('href');

        return $url;
    }

    private function getStateAndCity(): array
    {
        $text = $this->node->filter('.info-container__dealer-info > div > p')
            ->text(); // goiânia, go

        $parts = explode(', ', $text);

        if (count($parts) != 2) {
            throw new CarProcessIgnoreException('Invalid city/state: '.$text);
        }

        return $parts;
    }

    private function getYearAndYearModel(): array
    {
        $text = $this->node->filter('.info-container__car-info')
            ->children()
                ->eq(0)->text();

        list($year, $yearModel) = explode('/', $text);

        $year = intval($year);

        $yearModel = intval($yearModel);

        return [$year, $yearModel];
    }
}
