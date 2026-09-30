<?php

namespace App\Providers;

use App\Busca\Fts5MunicipioSearch;
use App\Busca\MunicipioSearch;
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
        $this->app->bind(MunicipioSearch::class, Fts5MunicipioSearch::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
