<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\OlxCar;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class HomeController extends Controller
{
    //
    public function index(): View
    {
        return view('index');
    }

    public function cars(Request $request): JsonResponse
    {
        $data = OlxCar::search($request);

        return response()->json($data);
    }

    public function redirect(Request $request): View {

        $request->validate([
            'brand' => ['required', 'string'],
            'model' => ['required', 'string'],
            'price' => ['required', 'numeric'],
            'year' => ['required', 'numeric'],
            'odometer' => ['required', 'numeric'],
        ]);

        $cars = OlxCar::query()
            ->where('active', true)
            ->where([
                ['brand', '=', $request->brand],
                ['model', '=', $request->model],
                ['year', '=', $request->year],
            ])
            ->where('price', '<=', $request->price)
            ->where('price', '>=', $request->price - 10000)
            ->where('odometer', '<=', $request->odometer)
            ->where('odometer', '>=', $request->odometer - 10000)
            ->get();

        return view('redirect', [
            'cars' => $cars
        ]);
    }
}
