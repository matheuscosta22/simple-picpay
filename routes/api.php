<?php

use App\Modules\Transaction\Http\Controllers\TransactionsController;
use App\Modules\User\Http\Controllers\LoginController;
use App\Modules\User\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::prefix('users')->group(function () {
    Route::post('/', [UsersController::class, 'store']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', [UsersController::class, 'index']);
        Route::get('/{userId}', [UsersController::class, 'show']);
        Route::post('/transactions', TransactionsController::class);
    });
});
Route::post('/login', LoginController::class);
