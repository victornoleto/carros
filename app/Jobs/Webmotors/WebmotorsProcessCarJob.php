<?php

namespace App\Jobs\Webmotors;

use App\Enums\CarProviderEnum;
use App\Jobs\CarProcess;
use App\Interfaces\CarProcessInterface;
use Illuminate\Support\Carbon;

class WebmotorsProcessCarJob extends CarProcess implements CarProcessInterface
{
    public function __construct(
        public string $brand,
        public string $model,
        public array $data
    ) {
        parent::__construct($brand, $model, CarProviderEnum::WEBMOTORS);
    }

    public function getVersion(): string|null
    {
        return $this->data['Specification']['Version']['Value'];
    }

    public function getYear(): int
    {
        return $this->data['Specification']['YearFabrication'];
    }

    public function getYearModel(): int|null
    {
        return $this->data['Specification']['YearModel'];
    }

    public function getPrice(): float
    {
        return $this->data['Prices']['Price'];
    }

    public function getOdometer(): int
    {
        return $this->data['Specification']['Odometer'];
    }

    public function getState(): string
    {
        $state = $this->data['Seller']['State'];

        $code = substr($state, -4);

        $code = str_replace(['(', ')'], '', $code);

        return $code;
    }

    public function getCity(): string
    {
        return $this->data['Seller']['City'];
    }

    public function getProviderId(): string
    {
        return $this->data['UniqueId'];
    }

    public function getProviderUpdatedAt(): Carbon
    {
        return now();
    }

    public function getProviderUrl(): string|null
    {
        return null;
    }

    public function getProcessIdentifier(): string
    {
        $data = [
            'UniqueId' => $this->data['UniqueId'],
            'Specification' => $this->data['Specification'],
            'Seller' => $this->data['Seller'],
        ];

        return json_encode($data);
    }
}
