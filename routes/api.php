<?php

use App\Http\Controllers\DivisionController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\TimeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Convención (blueprint): lecturas → GET con id en la ruta; escrituras →
| PATCH/POST. Todas las respuestas comparten el envelope { success, message, data }.
|
*/

Route::middleware('api')->group(function () {

    // Usuarios
    Route::prefix('users')->controller(UserController::class)->group(function () {
        Route::get('/{id}/ranking', 'ranking');   // tabla de posiciones de su división
        Route::get('/{id}', 'show');              // stats del usuario
        Route::patch('/{id}/coins', 'updateCoins');
        Route::patch('/{id}/lives', 'updateLives');
        Route::patch('/{id}/streak', 'updateStreak');
    });

    // Divisiones
    Route::prefix('divisions')->controller(DivisionController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/current/{userId}', 'current');
    });

    // Misiones
    Route::prefix('missions')->controller(MissionController::class)->group(function () {
        Route::get('/{userId}', 'index');
        Route::post('/progress', 'updateProgress');
    });

    // Tiempo del servidor
    Route::get('time', [TimeController::class, 'getTime']);
});
