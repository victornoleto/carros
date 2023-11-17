<?php

namespace App\Services\Webmotors;

use App\Services\CarProcessService;
use Illuminate\Support\Carbon;

class WebmotorsProcessService extends CarProcessService
{
    public function __construct(
        public string $brand,
        public string $model,
        public array $data,
    ) {
        parent::__construct($brand, $model);
    }

    public function getVersion(): string|null
    {
        return $this->data['version'];
    }

    public function getYear(): int
    {
        return explode('/', $this->data['year'])[0];
    }

    public function getYearModel(): int|null
    {
        return explode('/', $this->data['year'])[1];
    }

    public function getPrice(): float
    {
        $price = $this->data['price'];

        $price = str_replace('R$ ', '', $price);

        $price = str_replace('.', '', $price);

        $price = str_replace(',', '.', $price);

        return floatval($price);
    }

    public function getOdometer(): int
    {
        $odometer = $this->data['travelledDistance'];

        $odometer = str_replace(' km', '', $odometer);

        return intval($odometer);
    }

    public function getState(): string
    {
        return $this->getStateAndCity()[0];
    }

    public function getCity(): string
    {
        return $this->getStateAndCity()[1];
    }

    public function getProviderId(): string
    {
        return $this->data['id'];
    }

    public function getProviderUpdatedAt(): Carbon
    {
        return now();
    }

    public function getProviderUrl(): string|null
    {
        $year = $this->getYear();

        $yearModel = $this->getYearModel();

        $price = $this->getPrice();

        $odometer = $this->getOdometer();

        $minPrice = floor($price / 1000) * 1000;

        $maxPrice = ceil($price / 1000) * 1000;

        $minOdometer = floor($odometer / 1000) * 1000;

        $maxOdometer = ceil($odometer / 1000) * 1000;

        $brand = $this->brand;

        $model = $this->model;

        $url = "https://www.webmotors.com.br/carros/estoque/$brand/$model/de.$year/ate.$yearModel?tipoveiculo=carros&anoate=$yearModel&anode=$year&kmate=$maxOdometer&kmde=$minOdometer&marca1=$brand&modelo1=$model&precoate=$maxPrice&precode=$minPrice";

        return $url;
    }

    private function getStateAndCity(): array
    {

        $location = $this->data['location'];

        list($city, $state) = explode(' - ', $location);

        return [$state, $city];
    }
}
