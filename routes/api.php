<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\WalletController;
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

        Route::post('/add-money', [WalletController::class, 'addMoney']);
        Route::get('/currencies', [CurrencyController::class, 'index']);
        Route::get('/exchange-rates', [CurrencyController::class, 'exchangeRates']);
        Route::post('/convert-currency', [CurrencyController::class, 'convertCurrency']);
        Route::get('/transactions', [WalletController::class, 'transactions']);

        Route::group(['prefix' => 'trades'], function () {
            Route::post('/buy', [TradeController::class, 'buy']);
            Route::post('/sell', [TradeController::class, 'sell']);

            Route::get('/', [TradeController::class, 'transactions']);
            Route::get('/{trade:reference}', [TradeController::class, 'show']);
        });
    });
});

Route::fallback(function () {
    return response()->json([
        'message' => 'Route not found',
    ], 404);
});
