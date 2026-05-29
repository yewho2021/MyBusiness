<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(\Alexusmai\LaravelFileManager\FileManagerServiceProvider::class);
    }

    public function boot(): void
    {
        // Force HTTPS and clean root URL (no /public)
        URL::forceScheme('https');
        URL::forceRootUrl(config('app.url'));
    }
}
