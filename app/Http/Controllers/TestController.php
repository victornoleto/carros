<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Services\iCarros\iCarrosProcessService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class TestController extends Controller
{
    //
    public function index(string $provider, string $brand, string $model): Response
    {
        try {

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

        } catch (\Exception $e) {
            abort($e->getCode(), $e->getMessage());
        }
    }

    public function tmp() {

        $car = Car::where('state', 'GO')->first();

        $car->state = 'go';

        $car->save();

        /* $adContents = Storage::get('test.txt');

        $service = new iCarrosProcessService('honda', 'civic', $adContents);

        $data = $service->getData();

        dd($data); */
    }
}

