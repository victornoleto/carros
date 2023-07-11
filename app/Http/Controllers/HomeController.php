<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\OlxCar;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    //
    public function index(Request $request): View
    {
        if (!$request->has('models')) {

            $request->merge([
                'models' => [
                    'bmw 320i',
                    'audi a4',
                    'mercedes-benz c-180',
                    'toyota corolla',
                    'honda civic',
                    'mitsubishi lancer',
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
        $data = OlxCar::search($request);

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

        $query = OlxCar::query()
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
}
