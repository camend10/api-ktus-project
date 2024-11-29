<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        // Escuchar y registrar todas las consultas SQL
        // DB::listen(function ($query) {
        //     Log::info('Consulta ejecutada: ' . $query->sql);
        //     Log::info('Bindings: ', $query->bindings);
        //     Log::info('Tiempo de ejecución: ' . $query->time . ' ms');
        // });
    }
}
