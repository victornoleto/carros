<?php

namespace App\Jobs;

use App\Models\OlxCar;
use App\Models\OlxCarPrice;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class OlxProcessCarJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Crawler $node;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $brand,
        public string $model,
        public string $contents
    ) {
    }
    
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            $this->node = new Crawler($this->contents);
    
            list($url, $id) = $this->getCarUrlAndId();
    
            list($city, $state) = $this->getCarStateAndCity();
    
            list($price, $oldPrice) = $this->getCarPrice();
    
            $data = [
                'olx_id' => $id,
                'brand' => $this->brand,
                'model' => $this->model,
                //'title' => $this->getCarTitle(),
                'url' => $url,
                'odometer' => $this->getCarOdometer(),
                'year' => $this->getCarYear(),
                'price' => $price,
                'old_price' => $oldPrice,
                'state' => $state,
                'city' => $city,
                'olx_updated_at' => $this->getCarUpdatedAt(),
                'active' => true
            ];
    
            $this->save($data);

        } catch (\Exception $e) {

            $message = $e->getMessage();

            if ($message == 'Price not found.') {
                $this->log('Price not found.', 'error');

            } else {
                throw $e;
            }
        }
    }

    public function failed(\Throwable $exception)
    {
        // Send user notification of failure, etc...

        $this->log('Failed: '.$exception->getMessage(), 'error');

        $this->log($this->contents, 'error');
    }

    private function save(array $data)
    {

        $car = OlxCar::firstOrNew(
            ['olx_id' => $data['olx_id']],
        );

        $oldPrice = $car->price;

        foreach ($data as $key => $value) {
            $car->$key = $value;
        }

        $car->save();

        if ($oldPrice && $oldPrice != $car->price) {
            
            $this->log('Price changed from '.$oldPrice.' to '.$car->price);

            OlxCarPrice::create([
                'olx_car_id' => $car->id,
                'price' => $car->price,
                'old_price' => $oldPrice,
                'diff' => $car->price - $oldPrice,
                'olx_updated_at' => $car->olx_updated_at,
                'created_at' => Carbon::now()
            ]);
        }

        //OlxUpdateCarJob::dispatch($car)->onQueue('olx-update-car');

        $this->log("Car saved #".$car->id);
    }

    private function getCarTitle(): string
    {

        return $this->node->filter('h2.title')->text();
    }

    private function getCarUrlAndId(): array
    {

        $url = $this->node->filter('[data-ds-component="DS-NewAdCard-Link"]')->attr('href');

        $parts = explode('-', $url);

        $id = intval($parts[count($parts) - 1]);

        return [$url, $id];
    }

    private function getCarOdometer(): int
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

    private function getCarYear(): int
    {

        $elements = $this->node->filter('[data-testid="ds-adcard-content"]')
            ->children()
                ->eq(0)->children()
                    ->filter('ul li');

        $text = $elements->eq(1)->text();

        $year = intval($text);

        return $year;
    }

    private function getCarPrice(): array
    {

        $priceNode = $this->node->filter('h3.price');

        if (!$priceNode->count()) {
            throw new \Exception('Price not found.');
        }
        
        $price = $this->formatPriceStringToFloat($priceNode->text());

        $oldPriceNode = $this->node->filter('.old-price');

        if ($oldPriceNode->count()) {
            $oldPrice = $this->formatPriceStringToFloat($oldPriceNode->text());

        } else {
            $oldPrice = null;
        }

        return [$price, $oldPrice];
    }

    private function getCarStateAndCity(): array
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

    private function getCarUpdatedAt(): string
    {

        $elements = $this->node->filter('[data-testid="ds-adcard-content"]')
            ->children()->eq(1)
                ->children()->eq(0)
                    ->children()->eq(1)
                        ->filter('p');

        $text = $elements->eq(1)->text();

        $parts = explode(', ', $text);

        $parts[0] = $this->formatDateFromOlxFormat($parts[0]);

        $text = implode(' ', $parts).':00';

        return $text;
    }

    private function formatPriceStringToFloat(string $price): int
    {
            
        $price = str_replace('R$ ', '', $price);

        $price = str_replace('.', '', $price);

        $price = str_replace(',', '.', $price);

        $price = intval($price);

        return $price;
    }

    private function formatDateFromOlxFormat(string $date): string
    {

        if ($date == 'Hoje') {
            return date('Y-m-d');
        
        } elseif ($date == 'Ontem') {
            return date('Y-m-d', strtotime('-1 day'));
        
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

            return date('Y').'-'.$months[$month].'-'.$day;
        }
    }

    private function log(string $message, string $channel = 'debug'): void
    {

        Log::$channel('[OlxProcessCar]['.$this->brand.']['.$this->model.'] '.$message);
    }
}
