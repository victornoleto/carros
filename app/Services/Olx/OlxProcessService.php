<?php

namespace App\Services\Olx;

use App\Services\CarProcessService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class OlxProcessService extends CarProcessService
{
    public function __construct(
        string $brand,
        string $model,
        public array $data,
    ) {
        parent::__construct($brand, $model);
    }

    public function getData(): array
    {
        return parent::getData();
    }

    public function getVersion(): string|null
    {
        return $this->getProperty('vehicle_model');
    }

    public function getYear(): int
    {   
        return intval($this->getProperty('regdate'));
    }

    public function getYearModel(): int|null
    {
        return null;
    }

    public function getPrice(): float
    {
        $price = str_replace('R$ ', '', $this->data['price']);

        $price = str_replace('.', '', $price);

        $price = str_replace(',', '.', $price);

        $price = intval($price);

        return $price;
    }

    public function getOdometer(): int
    {
        return intval($this->getProperty('mileage'));
    }

    public function getState(): string
    {
        return $this->data['locationDetails']['uf'];
    }

    public function getCity(): string
    {
        return $this->data['locationDetails']['municipality'];
    }

    public function getProviderId(): string
    {
        return $this->data['listId'];
    }

    public function getProviderUpdatedAt(): Carbon
    {
        $ts = $this->data['date'];

        $date = Carbon::createFromTimestamp($ts. 'America/Sao_Paulo');

        return $date;
    }

    public function getProviderUrl(): string
    {
        return $this->data['url'];
    }

    private function getProperty(string $property): string|null
    {
        foreach ($this->data['properties'] as $row) {
            if ($row['name'] == $property) return $row['value'];
        }

        return null;
    }
}
