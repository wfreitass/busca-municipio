<?php

namespace Tests\Feature;

use App\Actions\PrepararBaseCenso;
use App\Models\Municipio;
use App\Models\Uf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('dados')]
final class TotaisOficiaisCensoTest extends TestCase
{
    public function test_read_model_real_confere_com_os_totais_do_ibge(): void
    {
        config(['database.connections.sqlite.database' => database_path('censo.sqlite')]);
        DB::purge('sqlite');

        if (! is_file(database_path('censo.sqlite')) || ! Schema::hasTable('uf_resumo')) {
            self::markTestSkipped('Base preparada ausente: rode `php artisan migrate --seed` (a imagem Docker já faz isso no build).');
        }

        self::assertSame(PrepararBaseCenso::TOTAIS_IBGE['ufs'], Uf::query()->count());
        self::assertSame(PrepararBaseCenso::TOTAIS_IBGE['municipios'], Municipio::query()->consultaveis()->count());
        self::assertSame(PrepararBaseCenso::TOTAIS_IBGE['populacao'], (int) Uf::query()->sum('populacao'));
        self::assertEqualsWithDelta(PrepararBaseCenso::TOTAIS_IBGE['area_km2'], (float) Uf::query()->sum('area_km2'), 1);
    }
}
