<?php

use App\Http\Controllers\DivisionController;
use App\Http\Controllers\TimeController;
use App\Http\Controllers\UserController;
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

Route::middleware('api')->group(function () {
    Route::controller(UserController::class)->group(
        function () {
            Route::post('listUsers', 'list');
            Route::post('getUser', 'getUser');
        }
    );

    Route::controller(DivisionController::class)->group(
        function () {
            Route::get('listDivisions', 'list');
            Route::post('getCurrentDivision', 'getCurrentDivision');
        }
    );

    Route::controller(TimeController::class)->group(
        function () {
            Route::get('getTimeLeftTillNextDay', 'getTimeLeftTillNextMidnight');
            Route::get('getTimeLeftTillNextWeek', 'getTimeLeftTillNextSunday20');
            Route::get('getTimeLeftTillNextMonth', 'getTimeLeftTillNextMonthEndMidnight');
        }
    );
});
