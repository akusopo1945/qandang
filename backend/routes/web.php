<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/download-qr/{id}', [\App\Http\Controllers\DownloadQrController::class, 'download'])->name('qr.download');

Route::get('/get-started', function () {
    return view('register');
});
