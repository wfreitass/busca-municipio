<?php

namespace Database\Seeders;

use App\Actions\PrepararBaseCenso;
use App\Models\Municipio;
use App\Models\Uf;
use Illuminate\Database\Seeder;

class ReadModelCensoSeeder extends Seeder
{
    public function run(PrepararBaseCenso $preparar): void
    {
        $preparar->handle(validarTotais: (bool) config('censo.validar_totais'));

        $this->command?->info(sprintf(
            'Read model pronto: %d UFs, %d municípios, %d habitantes, %.2f km².',
            Uf::query()->count(),
            Municipio::query()->consultaveis()->count(),
            (int) Uf::query()->sum('populacao'),
            (float) Uf::query()->sum('area_km2'),
        ));
    }
}
