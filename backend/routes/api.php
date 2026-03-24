<?php

use App\Http\Controllers\Api\LookupController;
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


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/coupon/validate', [CouponController::class, 'validateCoupon']);
Route::post('/auth/check-user', [AuthController::class, 'checkUserExists']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/auth/refresh-token', [AuthController::class, 'refreshToken']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
