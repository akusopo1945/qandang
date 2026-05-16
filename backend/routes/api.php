<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GoatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::get('/goats/{idOrQr}', [GoatController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // Goat API
    Route::get('/goats', [GoatController::class, 'index']);
    Route::post('/goats', [GoatController::class, 'store']);
    Route::post('/goats/{id}/weight', [GoatController::class, 'storeWeight']);
    Route::post('/goats/{id}/health', [GoatController::class, 'storeHealth']);
    Route::get('/goats/{id}/predict', [GoatController::class, 'predict']);
    Route::get('/export/goats', [GoatController::class, 'exportCsv']);
});
