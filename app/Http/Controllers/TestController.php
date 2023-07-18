<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class TestController extends Controller
{
    //
    public function index(string $provider, string $brand, string $model): Response
    {
        try {

            $page = request()->get('page', 1);

            $enum = \App\Enums\CarProviderEnum::fromValue($provider);
    
            $service = $enum->getSyncService();
            
            $results = $service->getPageResults($brand, $model, $page);

            $results = array_filter($results, function ($result) {
                return isset($result['result']);
            });

            $results = array_map(function ($result) {
                return $result['result'];
            }, $results);
    
            return response($results);

        } catch (\Exception $e) {
            abort($e->getCode(), $e->getMessage());
        }
    }

    public function tmp() {

        $provider = \App\Enums\CarProviderEnum::fromValue('olx');

        $syncService = $provider->getSyncService();
    
        $startTime = microtime(true);
    
        $pageResults = $syncService->getPageEntireResults('mitsubishi', 'lancer', 1);

        $unprocessedResults = $syncService->getPageUnprocessedResults($pageResults);

        dd($unprocessedResults);
    }
}
