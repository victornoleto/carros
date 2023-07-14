<?php

namespace App\Http\Controllers;

use App\Enums\CarProviderEnum;
use App\Enums\StateEnum;
use App\Models\Car;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    //
    public function dashboard(Request $request): View
    {

        $request->merge([
            'models' => [
                'peugeot 208',
                'fiat argo',
                'ford ka',
                'chevrolet onix',
                'volkswagen polo',
                'volkswagen gol',
                'volkswagen fox',
                'hyundai hb20',
            ]
        ]);

        return view('dashboard');
    }

    public function chartsData(Request $request): JsonResponse
    {
        $data = Car::getChartsData($request);

        $datasets = [];

        foreach ($data as $row) {

            $key = $row->brand . ' ' . $row->model;

            if (!isset($datasets[$key])) {

                $datasets[$key] = [
                    'label' => $key,
                    'data' => [],
                ];
            }

            $datasets[$key]['data'][] = [
                'x' => $row->odometer / 1000,
                'y' => $row->price / 1000,
                'r' => 10,
                'data' => $row
            ];
        }

        return response()->json(
            array_values($datasets)
        );
    }

    public function table(Request $request): View
    {
        $query = Car::search($request)
            ->orderBy('year', 'desc')
            ->orderBy('price', 'asc')
            ->orderBy('odometer', 'asc');

        $cars = $query->get();

        return view('table', [
            'cars' => $cars
        ]);
    }

    public function redirect(Car $car): RedirectResponse
    {
        if ($car->provider == CarProviderEnum::OLX) {
            $url = $car->provider_url;
        
        } elseif ($car->provider == CarProviderEnum::WEBMOTORS) {
            $url = $this->getWebmotorsRedirectUrl($car);
        }

        return redirect()->away($url);
    }

    private function getWebmotorsRedirectUrl(Car $car): string
    {
        $state = StateEnum::getValue($car->state);

        $maxOdometer = ceil($car->odometer / Car::$round) * Car::$round;
        $minOdometer = max($maxOdometer - Car::$round, 0);

        $maxPrice = ceil($car->price / Car::$round) * Car::$round;
        $minPrice = max($maxPrice - Car::$round, 0);

        $query = [
            'estadocidade' => "$state - $car->city",
            'marca1' => $car->brand,
            'modelo1' => $car->model,
            'tipoveiculo' => 'carros',
            'anode' => $car->year,
            'anoate' => $car->year + 1,
            'kmde' => $minOdometer,
            'kmate' => $maxOdometer,
            'precode' => $minPrice,
            'precoate' => $maxPrice,
        ];

        $url = "https://www.webmotors.com.br/carros/$car->state-$car->city/$car->brand/$car->model/de.$car->year/ate.$car->year";

        return $url . '?' . http_build_query($query);
    }
}
