<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Models\StateInsight;
use App\Services\GroqAIService;
use App\Services\SpreadsheetProcessorService;
use Illuminate\Support\Facades\Gate; // <--- ADD THIS LINE
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
            // Returns true if access_level is 1 (Admin) or higher
            // Adjust this logic if you have different levels (e.g. > 0)
            return $user->admin_access >= 1;
        });

        View::composer('components.header', function ($view) {
            $states = Cache::remember('header_states_list', 86400, function () {
                return StateInsight::orderBy('state', 'asc')->pluck('state');
            });

            $view->with('headerStates', $states);
        });
    }
}
