<?php

namespace App\Interfaces;

use Carbon\Carbon;

interface CarProcessInterface
{
	public function getVersion(): string|null;

	public function getYear(): int;

	public function getYearModel(): int|null;

	public function getPrice(): float;

	public function getOdometer(): int;

	public function getState(): string;

	public function getCity(): string;

	public function getProviderId(): string;

	public function getProviderUpdatedAt(): Carbon;

	public function getProviderUrl(): string|null;

	public function getProcessIdentifier(): string;
}