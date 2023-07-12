<?php

namespace App\Services\Webmotors;

use App\Enums\CarProviderEnum;
use App\Interfaces\CarProcessInterface;
use App\Traits\CarProcessTrait;
use Illuminate\Support\Carbon;

/**
 * Serviço para converter as informações contidas no conteúdo de um anúncio da Webmotors.
 *
 * @author Victor Noleto <victornoleto@sysout.com.br>
 * @since 12/07/2023 
 * @version 1.0.0
 */
class WebmotorsProcessService implements CarProcessInterface {

	use CarProcessTrait;

	public function __construct(
        public string $brand,
        public string $model,
        public array $data
    ) {
    }

	public function process(): array
    {
        return $this->getData();
    }

    public function getProvider(): string
    {
        return CarProviderEnum::WEBMOTORS;
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
        return $this->getStateAndCity()[0];
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
        return null;
    }

    public function getProcessIdentifier(): string
    {
        return json_encode($this->data);
    }

	private function getStateAndCity(): array {

		$location = $this->data['location'];

		list($city, $state) = explode(' - ', $location);

		return [$state, $city];
	}

}