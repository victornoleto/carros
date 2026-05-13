<?php

namespace App\Services\Webmotors;

use App\Enums\CarProviderEnum;
use App\Services\CarProcessService;
use Illuminate\Support\Carbon;

class WebmotorsProcessService extends CarProcessService
{
    public static function provider(): CarProviderEnum
    {
        return CarProviderEnum::WEBMOTORS;
    }

    public function __construct(
        public string $brand,
        public string $model,
        public array $data,
    ) {
        parent::__construct($brand, $model);
    }

    public function getVersion(): ?string
    {
        return $this->data['Specification']['Version']['Value'] ?? null;
    }

    public function getYear(): int
    {
        return (int) $this->data['Specification']['YearFabrication'];
    }

    public function getYearModel(): ?int
    {
        return (int) $this->data['Specification']['YearModel'];
    }

    public function getPrice(): float
    {
        return (float) $this->data['Prices']['Price'];
    }

    public function getOdometer(): int
    {
        return (int) $this->data['Specification']['Odometer'];
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
        return (string) $this->data['UniqueId'];
    }

    public function getProviderUpdatedAt(): Carbon
    {
        return now();
    }

    public function getProviderUrl(): ?string
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
        $city = $this->data['Seller']['City'];

        $state = $this->data['Seller']['State'];

        preg_match('/\(([A-Z]{2})\)$/', $state, $matches);

        return [$matches[1] ?? $state, $city];
    }
}
