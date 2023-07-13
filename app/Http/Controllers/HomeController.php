<?php

namespace App\Http\Controllers;

use App\Enums\CarProviderEnum;
use App\Enums\StateEnum;
use App\Models\Car;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class HomeController extends Controller
{
    //
    public function index(Request $request): View
    {
        if (!$request->has('models')) {

            $request->merge([
                'models' => [
                    /*'bmw 320i',
                    'audi a4',
                    'mercedes-benz c-180',
                    'toyota corolla',
                    'honda civic',
                    'mitsubishi lancer',*/
                    'toyota hilux',
                    'fiat toro',
                    'nissan frontier',
                    'chevrolet s10',
                    'mitsubishi l200',
                    'ford ranger',
                    'dodge ram',
                    'volkswagen amarok',
                ]
            ]);
        }

        if (!$request->has('states')) {

            $request->merge([
                'states' => ['GO']
            ]);
        }

        return view('index');
    }

    public function cars(Request $request): JsonResponse
    {
        $data = Car::search($request);

        return response()->json($data);
    }

    public function table(Request $request): View
    {
        if ($request->states && !is_array($request->states)) {

            $request->merge([
                'states' => explode(',', $request->states)
            ]);
        }

        if ($request->cities && !is_array($request->cities)) {

            $request->merge([
                'cities' => explode(',', $request->cities)
            ]);
        }

        if ($request->models && !is_array($request->models)) {

            $request->merge([
                'models' => explode(',', $request->models)
            ]);
        }

        $query = Car::query()
            ->where('active', true)
            ->where([
                ['year', '>=', $request->year_min],
                ['year', '<=', $request->year_max],
                ['price', '>=', $request->price_min * 1000],
                ['price', '<=', $request->price_max * 1000],
                ['odometer', '>=', $request->odometer_min * 1000],
                ['odometer', '<=', $request->odometer_max * 1000],
            ]);
        
        $query->when($request->states, function ($query) use ($request) {
            $query->whereIn('state', $request->states);
        });

        $query->when($request->cities, function ($query) use ($request) {

            $query->where(function ($query) use ($request) {

                foreach ($request->cities as $text) {

                    list($city, $state) = explode('/', $text);

                    $query->orWhere(function ($q) use ($city, $state) {
                        $q->where('city', $city)->where('state', $state);
                    });
                }

            });
        });

        $query->when($request->models, function ($query) use ($request) {

            $query->where(function ($query) use ($request) {

                foreach ($request->models as $text) {

                    list($brand, $model) = explode(' ', $text);

                    $query->orWhere(function ($q) use ($brand, $model) {
                        $q->where('brand', $brand)->where('model', $model);
                    });
                }

            });
        });

        $query->orderBy('year', 'desc')
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

    private function getWebmotorsRedirectUrl(Car $car)
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
            'anoate' => $car->year,
            'kmde' => $minOdometer,
            'kmate' => $maxOdometer,
            'precode' => $minPrice,
            'precoate' => $maxPrice,
        ];

        $url = "https://www.webmotors.com.br/carros/$car->state-$car->city/$car->brand/$car->model/de.$car->year/ate.$car->year";

        return $url . '?' . http_build_query($query);
    }
}
