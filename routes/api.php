<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\RiskController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\CurrencyController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/countries', [CountryController::class, 'index']);
Route::get('/countries/{id}/history', [CountryController::class, 'history']);
Route::get('/countries/{id}', [CountryController::class, 'show']);
Route::get('/risk', [RiskController::class, 'index']);
Route::get('/ports', [PortController::class, 'index']);
Route::get('/news', [NewsController::class, 'index']);
Route::get('/currency', [CurrencyController::class, 'index']);