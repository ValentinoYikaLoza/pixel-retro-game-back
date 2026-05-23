<?php

use App\Http\Controllers\DivisionController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\TimeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Contrato dirigido por el frontend (rutas RPC). Lecturas globales = GET;
| lecturas/escrituras con usuario = POST con el id en el body. Todas las
| respuestas comparten el envelope { success, message, data }.
|
*/

Route::middleware('api')->group(function () {

    // Tiempo del servidor (el cliente deriva mes y cuentas regresivas).
    Route::get('getTime', [TimeController::class, 'getTime']);

    // Juegos disponibles (catálogo).
    Route::get('listGames', [GameController::class, 'listGames']);

    // Partidas (autoridad del servidor: vidas, score, exp, misiones).
    Route::post('listGameLevels', [GameController::class, 'listGameLevels']);
    Route::post('startGame', [GameController::class, 'startGame']);
    Route::post('finishGame', [GameController::class, 'finishGame']);
    Route::post('doubleGameReward', [GameController::class, 'doubleGameReward']);
    Route::post('abandonGame', [GameController::class, 'abandonGame']);
    Route::post('getGameLeaderboard', [GameController::class, 'getGameLeaderboard']);

    // Usuario
    Route::post('getUser', [UserController::class, 'getUser']);
    Route::post('updateCoins', [UserController::class, 'updateCoins']);
    Route::post('updateLives', [UserController::class, 'updateLives']);
    Route::post('updateStreak', [UserController::class, 'updateStreak']);
    Route::post('updateExp', [UserController::class, 'updateExp']);

    // Leaderboard
    Route::get('listDivisions', [DivisionController::class, 'index']);
    Route::post('listUsers', [UserController::class, 'listUsers']);

    // Misiones
    Route::post('listMissions', [MissionController::class, 'index']);
    Route::post('updateProgress', [MissionController::class, 'updateProgress']);

    // Tienda
    Route::post('listAdvertisements', [ShopController::class, 'listAdvertisements']);
    Route::get('listCoinShop', [ShopController::class, 'listCoinShop']);
    Route::get('listLiveShop', [ShopController::class, 'listLiveShop']);
    Route::post('purchaseAdvertisement', [ShopController::class, 'purchaseAdvertisement']);
    Route::post('purchaseCoinShopItem', [ShopController::class, 'purchaseCoinShopItem']);
    Route::post('purchaseLiveShopItem', [ShopController::class, 'purchaseLiveShopItem']);
});
