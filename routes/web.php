<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarAlertController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'dashboard'])->name('dashboard');

Route::get('/charts-data', [HomeController::class, 'chartsData']);

Route::get('/table', [HomeController::class, 'table'])->name('table');
Route::get('/anuncios', [HomeController::class, 'table'])->name('listings');

Route::get('/redirect/{car}', [HomeController::class, 'redirect'])
    ->name('provider.redirect');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::patch('/car/{car}/ban', [CarController::class, 'ban'])->name('car.ban');
    Route::resource('alerts', CarAlertController::class)->only(['index', 'create', 'store', 'destroy']);
});

Route::middleware('can:view-debug-routes')->group(function () {
    Route::get('/test/{provider}/{brand}/{model}', [TestController::class, 'index']);
});
