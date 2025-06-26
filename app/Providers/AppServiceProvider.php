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
        // هذا الكود مفيد للروابط التي يتم إنشاؤها في الواجهات
        // أثناء تصفح الموقع عبر ngrok.
        if (! $this->app->runningInConsole()) {
            $ngrokHost = request()->header('X-Original-Host');
            if ($ngrokHost) {
                URL::forceRootUrl('https://' . $ngrokHost);
            }
        }
    }
}
