<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Reclamo;
use App\Observers\ReclamoObserver;

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
        Reclamo::observe(ReclamoObserver::class);
    }
}
