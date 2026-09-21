<?php

namespace Modules\Post\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Override;

class PostServiceProvider extends ServiceProvider
{
    #[Override]
    public function register()
    {
        // Log::info('PostServiceProvider loaded');
    }
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->mergeConfigFrom(__DIR__.'/../config/config.php','post');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }
}
