<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class TestController extends Controller
{
    //
    public function index(string $provider, string $brand, string $model): Response
    {
        $page = request()->get('page', 1);

        $enum = \App\Enums\CarProviderEnum::fromValue($provider);

        $service = $enum->getSyncService();
        
        $results = $service->getResults($brand, $model, $page);

        $results = array_filter($results, function ($result) {
            return $result['status'];
        });

        $results = array_map(function ($result) {
            return $result['car'];
        }, $results);

        return response($results);
    }
}
