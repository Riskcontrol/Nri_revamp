<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Models\StateInsight;

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
        // Share 'headerStates' with the header component specifically
        // We cache it for 24 hours (86400 seconds) to avoid database queries on every page load
        View::composer('components.header', function ($view) {
            $states = Cache::remember('header_states_list', 86400, function () {
                // Adjust the query if you need to fetch from a different model like tbldataentry
                return StateInsight::orderBy('state', 'asc')->pluck('state');
            });

            $view->with('headerStates', $states);
        });
    }
}
