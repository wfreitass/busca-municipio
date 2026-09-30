<?php

namespace App\Providers;

use App\Busca\BuscaMunicipios;
use App\Busca\Fts5BuscaMunicipios;
use App\Console\Commands\PrepararCenso;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->commands([PrepararCenso::class]);

        // Trocar o motor de busca = trocar esta linha.
        $this->app->bind(BuscaMunicipios::class, Fts5BuscaMunicipios::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
