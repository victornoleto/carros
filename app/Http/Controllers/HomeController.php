<?php

namespace App\Http\Controllers;

use App\Enums\CarProviderEnum;
use App\Models\Car;
use App\Services\Webmotors\WebmotorsSyncService;
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
                /* 'peugeot 208',
                'fiat argo',
                'ford ka',
                'chevrolet onix',
                'volkswagen polo',
                'volkswagen gol',
                'volkswagen fox',
                'hyundai hb20', */
                'mercedes-benz c-180',
                'bmw 320i',
                'audi a4',
                'volkswagen jetta',
                'mitsubishi lancer',
                'honda civic',
                'toyota corolla',
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
            return redirect()->away($car->provider_url);
        
        } elseif ($car->provider == CarProviderEnum::WEBMOTORS) {
            return $this->redirectToWebmotorsAdPage($car);
        }
    }

    private function redirectToWebmotorsAdPage(Car $car): RedirectResponse
    {
        $parts = [
            'https://www.webmotors.com.br/comprar',
            $car->brand,
            $car->model,
            slugify($car->version),
            '4-portas',
            $car->year.'-'.$car->year_model,
            $car->provider_id
        ];

        $url = implode('/', $parts);

        return redirect()->away($url, 302, WebmotorsSyncService::getHeaders());
    }
}
