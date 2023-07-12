<?php

namespace App\Services\iCarros;

use App\Enums\CarProviderEnum;
use App\Interfaces\CarProcessInterface;
use App\Traits\CarProcessTrait;
use Illuminate\Support\Carbon;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Serviço para converter as informações contidas no conteúdo de um anúncio da iCarros.
 *
 * @author Victor Noleto <victornoleto@sysout.com.br>
 * @since 12/07/2023 
 * @version 1.0.0
 */
class iCarrosProcessService implements CarProcessInterface
{
    use CarProcessTrait;

    public Crawler $node;

    public function __construct(
        public string $brand,
        public string $model,
        public string $contents
    ) {
    }

    public function process(): array {

        $this->node = new Crawler($this->contents);

        return $this->getData();
    }

    public function getProvider(): string
    {
        return CarProviderEnum::ICARROS;
    }
    
    public function getProcessIdentifier(): string
    {
        return $this->contents;
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
        return $this->node->filter('li.offer-card')
            ->attr('data-anuncioid');
    }

    public function getProviderUpdatedAt(): Carbon
    {
        return Carbon::now();
    }

    public function getProviderUrl(): string
    {
        $url = $this->node->filter('.offer-card__image-container')
            ->attr('href');

        return iCarrosSyncService::$url.$url;
    }

    private function getStateAndCity(): array
    {
        $text = $this->node->filter('.info-container__dealer-info > div > p')
            ->text();

        list($city, $state) = explode(', ', $text);

        return [$city, $state];
    }

    private function getYearAndYearModel(): array {

        $text = $this->node->filter('.info-container__car-info')
            ->children()
                ->eq(0)->text();

        list($year, $yearModel) = explode('/', $text);

        $year = intval($year);

        $yearModel = intval($yearModel);

        return [$year, $yearModel];
    }
}
