<?php

use App\Http\Controllers\CarController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'dashboard']);

Route::get('/charts-data', [HomeController::class, 'chartsData']);

Route::get('/table', [HomeController::class, 'table']);

Route::get('/redirect/{car}', [HomeController::class, 'redirect'])
    ->name('provider.redirect');

Route::get('/car/{car}/ban', [CarController::class, 'ban'])
    ->name('car.ban');

Route::get('/test/{provider}/{brand}/{model}', [TestController::class, 'index']);

Route::get('/tmp', [TestController::class, 'tmp']);
