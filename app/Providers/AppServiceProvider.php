<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

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
        Carbon::setLocale('es'); // Configura Carbon en español
        setlocale(LC_TIME, 'es_ES.UTF-8'); // Opcional para formateo de fechas con strftime()
    }
}
