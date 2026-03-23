<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['prefix' => 'lookup'], function () {
    Route::get('/states', [LookupController::class, 'states']);
    Route::get('/cities', [LookupController::class, 'citiesByState']);
    Route::get('/samaj', [LookupController::class, 'samaj']);
    Route::get('/occupations', [LookupController::class, 'occupations']);
    Route::get('/educations', [LookupController::class, 'educations']);
    Route::get('/search-cities', [LookupController::class, 'searchCities']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
