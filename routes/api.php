<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\TradeController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::get('/', function () {
        return response()->json([
            'message' => 'Hello World',
        ], 200);
    });

    // Auth routes
    Route::group(['prefix' => 'auth'], function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
    });

    // Routes with auth middleware
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);

        Route::get('/currencies', [CurrencyController::class, 'index']);
        Route::get('/exchange-rates', [CurrencyController::class, 'exchangeRates']);
        Route::post('/convert-currency', [CurrencyController::class, 'convertCurrency']);

        Route::group(['prefix' => 'trades'], function () {
            Route::post('/buy', [TradeController::class, 'buy']);
            Route::post('/sell', [TradeController::class, 'sell']);
        });
    });
});

Route::fallback(function () {
    return response()->json([
        'message' => 'Route not found',
    ], 404);
});
