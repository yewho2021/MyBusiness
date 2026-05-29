<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\CauserResolver;
use App\Models\Admin;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(\Alexusmai\LaravelFileManager\FileManagerServiceProvider::class);
    }

    public function boot(): void
    {
        // Force HTTPS only in production or when explicitly enabled.
        // APP_FORCE_HTTPS=true in .env overrides, otherwise auto-detect from APP_ENV.
        if (config('app.force_https', app()->isProduction())) {
            URL::forceScheme('https');
        }
        URL::forceRootUrl(config('app.url'));

        // ── Activity Log: resolve causer from cookie auth ──
        app(CauserResolver::class)->resolveUsing(function () {
            // Prefer the singleton set by AdminAuthenticate middleware
            $admin = request()->attributes->get('admin');
            if ($admin) {
                return $admin;
            }

            // Fallback: decrypt the cookie (for contexts outside middleware)
            $raw = request()->cookie('admin_id');
            if (!$raw) return null;

            try {
                $adminId = (int) decrypt($raw);
            } catch (\Exception $e) {
                $adminId = is_numeric($raw) ? (int) $raw : null;
            }

            return $adminId ? Admin::find($adminId) : null;
        });
    }
}
