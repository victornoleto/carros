<?php

namespace App\Services;

abstract class CarSyncService {

	abstract function getPageResult(string $brand, string $model, int $page = 1): string|array;

	abstract function getAdResults(string $pageResult): array;
}