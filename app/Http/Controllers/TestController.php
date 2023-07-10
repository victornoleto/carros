<?php

namespace App\Http\Controllers;

use App\Jobs\OlxProcessCarJob;
use App\Jobs\OlxUpdateCarJob;
use App\Models\OlxCar;
use App\Services\OlxService;
use App\Services\WebmotorsService;
use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler; 

class TestController extends Controller
{
    //
    public function index(OlxService $service) {

        $car = OlxCar::find(375);

        OlxUpdateCarJob::dispatchSync($car);
    }
}
