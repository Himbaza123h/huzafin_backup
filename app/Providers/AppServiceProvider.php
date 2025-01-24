<?php

namespace App\Providers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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
        Schema::defaultStringLength(191);
        Response::macro('success', function ($data = [], $message = '') {
            return response()->make([
                'status' => true,
                'data' => $data,
                'message' => $message
            ]);
        });
        Response::macro('error', function ($error = null, $message = '', $status_code = 400) {
            return response()->make([
                'status' => false,
                'errors' => $error,
                'message' => $message,
            ], $status_code);
        });
    }
}
