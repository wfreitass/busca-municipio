<?php

namespace Tests\Concerns;

use Database\Seeders\ReadModelCensoSeeder;
use Illuminate\Support\Facades\DB;

/**
 * Cria um SQLite temporário só com as tabelas cruas do censo, aponta a conexão para ele
 * e prepara o read model do jeito da aplicação: migrate + ReadModelCensoSeeder.
 */
trait UsaBaseCenso
{
    private ?string $arquivoBaseCenso = null;

    protected function criarBaseCenso(string $inserts): void
    {
        $this->arquivoBaseCenso = (string) tempnam(sys_get_temp_dir(), 'censo-fixture-');
        config(['database.connections.sqlite.database' => $this->arquivoBaseCenso]);
        DB::purge('sqlite');

        DB::unprepared(<<<'SQL'
            CREATE TABLE uf (cd_uf TEXT PRIMARY KEY, nm_uf TEXT NOT NULL) WITHOUT ROWID;
            CREATE TABLE municipio (cd_mun TEXT PRIMARY KEY, nm_mun TEXT NOT NULL, cd_uf TEXT NOT NULL) WITHOUT ROWID;
            CREATE TABLE setor (cd_setor TEXT PRIMARY KEY, cd_mun TEXT NOT NULL, situacao TEXT, area_km2 REAL, populacao INTEGER) WITHOUT ROWID;
            CREATE TABLE demografia (cd_setor TEXT PRIMARY KEY, moradores INTEGER, homens INTEGER, mulheres INTEGER) WITHOUT ROWID;
        SQL);
        DB::unprepared($inserts);

        $this->artisan('migrate', ['--force' => true]);
        $this->prepararBaseCenso();
    }

    protected function prepararBaseCenso(): void
    {
        $this->seed(ReadModelCensoSeeder::class);
    }

    protected function tearDown(): void
    {
        DB::purge('sqlite');
        if ($this->arquivoBaseCenso !== null) {
            @unlink($this->arquivoBaseCenso);
        }

        parent::tearDown();
    }
}
