<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        View::share('appName', Setting::get('app_name', 'PiCBT'));

        RateLimiter::for('exam_autosave', fn (Request $request) =>
            Limit::perMinute(120)->by($request->user()?->id ?: $request->ip())
        );

        RateLimiter::for('exam_cheat', fn (Request $request) =>
            Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())
        );

        RateLimiter::for('exam_mutations', fn (Request $request) =>
            Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())
        );
    }
}
