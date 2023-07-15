<?php

namespace App\Services;

use App\Enums\CarProviderEnum;
use Illuminate\Support\Carbon;

abstract class CarProcessService
{

    public function __construct(
        public string $brand,
        public string $model
    ) {
    }

    public function getData(): array
    {

        dd($this);

        $data = [
            'brand' => $this->brand,
            'model' => $this->model,
            'version' => $this->getVersion(),
            'year' => $this->getYear(),
            'year_model' => $this->getYearModel(),
            'price' => $this->getPrice(),
            'odometer' => $this->getOdometer(),
            'state' => $this->getState(),
            'city' => $this->getCity(),
            'provider' => $this->getProvider()->value,
            'provider_id' => $this->getProviderId(),
            'provider_updated_at' => $this->getProviderUpdatedAt()->toDateTimeString(),
            'provider_url' => $this->getProviderUrl(),
        ];

        return $data;
    }

    abstract public function getProvider(): CarProviderEnum;

    abstract public function getVersion(): string|null;

    abstract public function getYear(): int;

    abstract public function getYearModel(): int|null;

    abstract public function getPrice(): float;

    abstract public function getOdometer(): int;

    abstract public function getState(): string;

    abstract public function getCity(): string;

    abstract public function getProviderId(): string;

    abstract public function getProviderUpdatedAt(): Carbon;

    abstract public function getProviderUrl(): string|null;
}
