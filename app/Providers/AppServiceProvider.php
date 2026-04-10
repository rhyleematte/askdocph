<?php

namespace App\Providers;

use App\Models\DailyAffirmation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('partials.daily_affirmation_panel', function ($view) {
            $view->with('currentDailyAffirmation', DailyAffirmation::current());
        });
    }
}
