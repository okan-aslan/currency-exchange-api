<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('profile/logout', [AuthController::class, 'logout']);
    Route::post('profile/delete', [AuthController::class, 'destroy']);

    Route::get('accounts', [AccountController::class, 'getAllAccounts']);
    Route::post('accounts', [AccountController::class, 'createAccount']);
    Route::get('/accounts/{accountNo}/convert/{targetCurrency}', [AccountController::class, 'getAccountValue']);
    Route::post('accounts/{accountNo}/deposit', [AccountController::class, 'deposit']);
    Route::post('accounts/{accountNo}/withdraw', [AccountController::class, 'withdraw']);
    Route::get('accounts/{accountNo}', [AccountController::class, 'showAccount']);
    Route::delete('accounts/{accountNo}', [AccountController::class, 'deleteAccount']);
});
