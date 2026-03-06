<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use App\Models\StateInsight;
use App\Services\GroqAIService;
use App\Services\SpreadsheetProcessorService;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GroqAIService::class);
        $this->app->singleton(SpreadsheetProcessorService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('admin-access', function (User $user) {
            return $user->admin_access >= 1;
        });

        View::composer('components.header', function ($view) {
            $states = Cache::remember('header_states_list', 86400, function () {
                return StateInsight::orderBy('state', 'asc')->pluck('state');
            });

            $view->with('headerStates', $states);
        });

        // ── Named rate limiters ───────────────────────────────────────────────
        // These complement the route-level throttle middleware and allow the
        // RegisterController to call RateLimiter::tooManyAttempts() by name.

        // Registration: 3 attempts per IP per hour
        RateLimiter::for('register', function (Request $request) {
            return Limit::perHour(3)->by($request->ip());
        });

        // Login: 10 attempts per IP per minute (existing behaviour — keep in sync)
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // Password reset: 5 per IP per 10 minutes (keep in sync with route throttle)
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinutes(10, 5)->by($request->ip());
        });
    }
}
