<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;

class CarController extends Controller
{
    //
    public function ban(Car $car) {

        $car->update([
            'banned' => true
        ]);

        return redirect()->back();
    }
}
