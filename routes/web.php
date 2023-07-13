<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

Route::get('/test', [TestController::class, 'index']);

Route::get('/', [HomeController::class, 'index']);

Route::get('/charts-data', [HomeController::class, 'chartsData']);

Route::get('/table', [HomeController::class, 'table']);

Route::get('/redirect/{car}', [HomeController::class, 'redirect'])
    ->name('provider.redirect');

Route::get('/test/{provider}/{brand}/{model}', [TestController::class, 'index']);

Route::get('/tmp', [TestController::class, 'tmp']);
