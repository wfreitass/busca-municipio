<?php

namespace App\Providers;

use App\Busca\BuscaMunicipios;
use App\Busca\Fts5BuscaMunicipios;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Trocar o motor de busca = trocar esta linha.
        $this->app->bind(BuscaMunicipios::class, Fts5BuscaMunicipios::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // migrate:fresh, migrate:reset/refresh/rollback e db:wipe apagariam também as tabelas cruas
        // do censo (o dado original). O read model pode ser refeito; o dado original não.
        DB::prohibitDestructiveCommands();
    }
}
