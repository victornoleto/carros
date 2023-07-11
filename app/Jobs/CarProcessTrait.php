<?php

namespace App\Jobs;

trait CarProcessTrait {

	public function getCarData(): array {

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
			'provider' => $this->provider,
			'provider_id' => $this->getProviderId(),
			'provider_updated_at' => $this->getProviderUpdatedAt()->toDateTimeString(),
			'provider_url' => $this->getProviderUrl(),
		];

		foreach ($data as $key => $value) {

			if (is_string($value) && $key != 'provider_id') {
				$data[$key] = mb_strtolower($value);
			}
		}

		return $data;
	}
}