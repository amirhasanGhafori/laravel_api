<?php

namespace Modules\Post\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ProvidersRouteServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ProvidersRouteServiceProvider
{
    public function boot()
    {
        $this->routes(function () {
            Route::middleware('web')->group(__DIR__.'/../routes/web.php');
        });
    }
}