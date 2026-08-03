<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\DownloadQrController;

Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/catalog', [PublicController::class, 'catalog'])->name('catalog');
Route::get('/catalog/{id}', [PublicController::class, 'show'])->name('catalog.show');

Route::get('/wishlist', [\App\Http\Controllers\WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/add', [\App\Http\Controllers\WishlistController::class, 'add'])->name('wishlist.add');
Route::delete('/wishlist/{id}', [\App\Http\Controllers\WishlistController::class, 'remove'])->name('wishlist.remove');

Route::middleware('auth')->group(function () {
    Route::get('/cart', [\App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [\App\Http\Controllers\CartController::class, 'add'])->name('cart.add');
    Route::delete('/cart/{id}', [\App\Http\Controllers\CartController::class, 'remove'])->name('cart.remove');
    Route::post('/checkout', [\App\Http\Controllers\CartController::class, 'checkout'])->name('checkout');
});

Route::get('/download-qr/{id}', [DownloadQrController::class, 'download'])->name('qr.download');

Route::get('/get-started', function () {
    return view('register');
})->name('register');

Route::get('/login', function () {
    return view('register');
})->name('login');

// Auth routes logic usually handled by Filament for admin, 
// but for public marketplace we might need separate auth or use same.
// For now, let's keep it simple.
