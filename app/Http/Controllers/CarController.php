<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\RedirectResponse;

class CarController extends Controller
{
    //
    public function ban(Car $car): RedirectResponse
    {

        $car->update([
            'banned' => true,
        ]);

        return redirect()->back();
    }
}
