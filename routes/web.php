<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

Route::get('/test', [TestController::class, 'index']);

Route::get('/', [HomeController::class, 'index']);

Route::get('/cars', [HomeController::class, 'cars']);

Route::get('/redirect', [HomeController::class, 'redirect']);