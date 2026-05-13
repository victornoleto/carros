<?php

namespace App\Services;

use App\Enums\CarProviderEnum;
use Illuminate\Support\Carbon;

abstract class CarProcessService
{
    public CarProviderEnum $provider;

    public function __construct(
        public string $brand,
        public string $model
    ) {
        $this->provider = static::provider();
    }

    abstract public static function provider(): CarProviderEnum;

    public function getData(): array
    {
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
            'provider' => $this->provider->value,
            'provider_id' => $this->getProviderId(),
            'provider_updated_at' => $this->getProviderUpdatedAt()->toDateTimeString(),
            'provider_url' => $this->getProviderUrl(),
        ];

        return $data;
    }

    abstract public function getVersion(): ?string;

    abstract public function getYear(): int;

    abstract public function getYearModel(): ?int;

    abstract public function getPrice(): float;

    abstract public function getOdometer(): int;

    abstract public function getState(): string;

    abstract public function getCity(): string;

    abstract public function getProviderId(): string;

    abstract public function getProviderUpdatedAt(): Carbon;

    abstract public function getProviderUrl(): ?string;
}
