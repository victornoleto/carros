<?php

namespace App\Http\Controllers;

use App\Services\WebmotorsService;
use Illuminate\Http\Request;

class TestController extends Controller
{
    //
    public function index(WebmotorsService $service) {

        $result = $service->sync('honda', 'civic');
    }
}
