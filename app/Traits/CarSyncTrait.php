<?php

namespace App\Traits;

trait CarSyncTrait {

	public function getResults(string $brand, string $models, int $page = 1): array 
    {
        $pageResult = $this->getPageResult($brand, $models, $page);

        $adResults = $this->getAdResults($pageResult);

        $result = [];

        foreach ($adResults as $adResult) {

            $row = [
                'data' => $adResult
            ];

            try {

                $data = $this->getAdData($brand, $models, $adResult);

				$row['car'] = $data;
				
            } catch (\Exception $e) {
                $row['error'] = $e->getMessage();
            }

			$row['status'] = isset($row['car']);

            array_push($result, $row);
        }

        return $result;
    }
}