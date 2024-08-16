<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('profile/logout', [AuthController::class, 'logout']);
    Route::post('profile/delete', [AuthController::class, 'destroy']);

    Route::prefix('accounts')->group(function () {
        Route::get('/', [AccountController::class, 'getAllAccounts']);
        Route::post('/', [AccountController::class, 'createAccount']);
        Route::delete('/', [AccountController::class, 'deleteAccount']);
        Route::get('/show', [AccountController::class, 'showAccount']);
        Route::get('/convert', [AccountController::class, 'getAccountValue']);
        Route::post('/deposit', [TransactionController::class, 'deposit']);
        Route::post('/withdraw', [TransactionController::class, 'withdraw']);
    });
});

Route::post('currency/convert', [AccountController::class, 'convertCurrency']);
