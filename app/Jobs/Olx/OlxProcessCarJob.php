<?php

namespace App\Jobs\Olx;

use App\Enums\CarProviderEnum;
use App\Interfaces\CarProcessInterface;
use App\Jobs\CarProcess;
use App\Jobs\CarProcessIgnoreException;
use App\Models\Car;
use Carbon\Carbon;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Processar anúncio da Olx.
 *
 * @author Victor Noleto <victornoleto@sysout.com.br>
 * @since 11/07/2023 
 * @version 1.0.0
 */
class OlxProcessCarJob extends CarProcess implements CarProcessInterface {

    public Crawler $node;

    public function __construct(
        string $brand,
        string $model,
        public string $contents
    ) {
        parent::__construct($brand, $model, CarProviderEnum::OLX);
    }
    
    public function handle(): void
    {
        $this->node = new Crawler($this->contents);

        parent::handle();
    }

    public function onCarUpdated(Car $car) {

    }

    public function getProcessIdentifier(): string
    {
        return $this->contents;
    }

    public function getVersion(): string|null
    {
        return null;
    }

    public function getYear(): int
    {
        $elements = $this->node->filter('[data-testid="ds-adcard-content"]')
            ->children()
                ->eq(0)->children()
                    ->filter('ul li');

        $text = $elements->eq(1)->text();

        $year = intval($text);

        return $year;
    }

    public function getYearModel(): int|null
    {
        return null;
    }

    public function getPrice(): float
    {
        $priceNode = $this->node->filter('h3.price');

        if (!$priceNode->count()) {
            throw new CarProcessIgnoreException('Price not found.');
        }
        
        $price = str_replace('R$ ', '', $priceNode->text());

        $price = str_replace('.', '', $price);

        $price = str_replace(',', '.', $price);

        $price = intval($price);

        return $price;
    }

    public function getOdometer(): int
    {
        $elements = $this->node->filter('[data-testid="ds-adcard-content"]')
            ->children()
                ->eq(0)->children()
                    ->filter('ul li');

        $text = $elements->eq(0)->text();

        $text = str_replace(' km', '', $text);
        
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
        return $this->getUrlAndId()[1];
    }

    public function getProviderUpdatedAt(): Carbon
    {
        $elements = $this->node->filter('[data-testid="ds-adcard-content"]')
            ->children()->eq(1)
                ->children()->eq(0)
                    ->children()->eq(1)
                        ->filter('p');

        $text = $elements->eq(1)->text();

        $parts = explode(', ', $text);

        $date = $parts[0];

        if ($date == 'Hoje') {
            $date = date('Y-m-d');
        
        } elseif ($date == 'Ontem') {
            $date = date('Y-m-d', strtotime('-1 day'));
        
        } else {

            $months = [
                'jan' => '01',
                'fev' => '02',
                'mar' => '03',
                'abr' => '04',
                'mai' => '05',
                'jun' => '06',
                'jul' => '07',
                'ago' => '08',
                'set' => '09',
                'out' => '10',
                'nov' => '11',
                'dez' => '12',
            ];

            list($day, $month) = explode(' de ', $date);

            $day = str_pad($day, 2, '0', STR_PAD_LEFT);

            $date = date('Y').'-'.$months[$month].'-'.$day;
        }

        return Carbon::createFromFormat('Y-m-d H:i:s', $date.' '.$parts[1].':00');
    }

    public function getProviderUrl(): string
    {
        return $this->getUrlAndId()[0];
    }

    private function getUrlAndId(): array
    {

        $url = $this->node->filter('[data-ds-component="DS-NewAdCard-Link"]')->attr('href');

        $parts = explode('-', $url);

        $id = intval($parts[count($parts) - 1]);

        return [$url, $id];
    }

    private function getStateAndCity(): array
    {

        $elements = $this->node->filter('[data-testid="ds-adcard-content"]')
            ->children()->eq(1)
                ->children()->eq(0)
                    ->children()->eq(1)
                        ->filter('p');

        $text = $elements->eq(0)->text();

        $parts = explode(' - ', $text);

        return $parts;
    }
}
