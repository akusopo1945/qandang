<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for Qandang
|--------------------------------------------------------------------------
*/

Route::get('/status', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Qandang API is running',
        'version' => '1.0.0'
    ]);
});

Route::prefix('goats')->group(function () {
    Route::get('/', function () {
        // Return list of goats
        return response()->json([]);
    });

    Route::get('/{id}', function ($id) {
        // Return single goat details
        return response()->json(['id' => $id]);
    });

    Route::post('/{id}/weight', function (Request $request, $id) {
        // Record new weight
        return response()->json(['status' => 'recorded']);
    });
});

Route::prefix('health')->group(function () {
    Route::get('/records/{goat_id}', function ($goat_id) {
        return response()->json([]);
    });
});
