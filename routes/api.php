<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShareController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/signs-list', [ShareController::class, 'signsList']);

Route::middleware('jwt.verify')->group(function() {
    Route::prefix('shares')->group(function () {
        Route::get('/', [ShareController::class, 'index']);
        Route::post('/store', [ShareController::class, 'store']);
        Route::post('/update', [ShareController::class, 'update']);
        Route::post('/get-stats', [ShareController::class, 'getStats']);
    });
});