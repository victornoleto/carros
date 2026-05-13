<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Throwable;

class HomeController extends Controller
{
    //
    public function dashboard(Request $request): View
    {
        try {
            $cars = Car::search($request)
                ->latest('provider_updated_at')
                ->limit(220)
                ->get();

            $totalCars = Car::query()->where('active', true)->where('banned', false)->count();
            $databaseUnavailable = false;
        } catch (Throwable $exception) {
            report($exception);

            $cars = collect();
            $totalCars = 0;
            $databaseUnavailable = true;
        }

        return view('dashboard', [
            'cars' => $cars,
            'totalCars' => $totalCars,
            'databaseUnavailable' => $databaseUnavailable,
        ]);
    }

    public function chartsData(Request $request): JsonResponse
    {
        $data = Car::getChartsData($request);

        $datasets = [];

        foreach ($data as $row) {

            $key = $row->brand.' '.$row->model;

            if (! isset($datasets[$key])) {

                $datasets[$key] = [
                    'label' => $key,
                    'data' => [],
                ];
            }

            $datasets[$key]['data'][] = [
                'x' => $row->odometer / 1000,
                'y' => $row->price / 1000,
                'r' => 10,
                'data' => $row,
            ];
        }

        return response()->json(
            array_values($datasets)
        );
    }

    public function table(Request $request): View
    {
        try {
            $query = Car::search($request)
                ->when($request->filled('q'), function ($query) use ($request) {
                    $term = mb_strtolower((string) $request->string('q'));

                    $query->where(function ($query) use ($term) {
                        $query->where('brand', 'like', "%{$term}%")
                            ->orWhere('model', 'like', "%{$term}%")
                            ->orWhere('version', 'like', "%{$term}%");
                    });
                })
                ->latest('provider_updated_at')
                ->orderBy('price', 'asc')
                ->orderBy('odometer', 'asc');

            $cars = $query->paginate(50)->withQueryString();
            $databaseUnavailable = false;
        } catch (Throwable $exception) {
            report($exception);

            $cars = new LengthAwarePaginator([], 0, 50, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
            $databaseUnavailable = true;
        }

        return view('table', [
            'cars' => $cars,
            'databaseUnavailable' => $databaseUnavailable,
        ]);
    }

    public function redirect(Car $car): RedirectResponse
    {
        return redirect()->away($car->provider_url);
    }
}
