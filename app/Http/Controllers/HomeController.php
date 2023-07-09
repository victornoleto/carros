<?php

namespace App\Http\Controllers;

use App\Models\Car;
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
        $data = Car::search($request);

        return response()->json($data);
    }

    public function redirect(Request $request): RedirectResponse {

        $request->validate([
            'brand' => ['required', 'string'],
            'model' => ['required', 'string'],
            'price' => ['required', 'numeric'],
            'year_fabrication' => ['required', 'numeric'],
            'odometer' => ['required', 'numeric'],
        ]);

        $url = "https://www.webmotors.com.br/carros/estoque/".$request->brand."/".$request->model."?";
            
        $query = [
            "anoate" => $request->year_fabrication,
            "anode" => $request->year_fabrication,
            "kmde" => max($request->odometer - 10000, 0),
            "kmate" => $request->odometer,
            "precode" => max($request->price - 10000, 0),
            "precoate" => $request->price
        ];

        foreach ($query as $key => $value) {
            $url .= $key."=".$value."&";
        }

        return Redirect::away($url);
    }
}
