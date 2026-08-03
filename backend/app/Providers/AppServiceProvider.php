<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (!app()->runningInConsole()) {
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
            $proto = ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' ? 'https' : 'http';
            URL::forceRootUrl($proto . '://' . $host);
            URL::forceScheme($proto);
        } else {
            URL::forceRootUrl(config('app.url'));
            if (str_starts_with(config('app.url'), 'https://')) {
                URL::forceScheme('https');
            }
        }

        view()->composer('*', function ($view) {
            if (auth()->check()) {
                $view->with('cart_count', \App\Models\Cart::where('user_id', auth()->id())->count());
                $view->with('wishlist_count', \App\Models\Wishlist::where('user_id', auth()->id())->count());
            } else {
                $view->with('cart_count', 0);
                $view->with('wishlist_count', count(session()->get('wishlist', [])));
            }
        });
    }
}
