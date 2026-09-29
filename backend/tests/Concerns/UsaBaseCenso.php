<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PDO;

/**
 * Cria um SQLite temporário com o schema cru do censo, roda censo:preparar
 * e (opcionalmente) aponta a conexão padrão para ele.
 */
trait UsaBaseCenso
{
    private ?string $arquivoBaseCenso = null;

    protected function criarBaseCenso(string $inserts, bool $usarComoConexao = true): string
    {
        $arquivo = (string) tempnam(sys_get_temp_dir(), 'censo-fixture-');
        $this->arquivoBaseCenso = $arquivo;

        $db = new PDO('sqlite:'.$arquivo);
        $db->exec(<<<'SQL'
            CREATE TABLE uf (cd_uf TEXT PRIMARY KEY, nm_uf TEXT NOT NULL) WITHOUT ROWID;
            CREATE TABLE municipio (cd_mun TEXT PRIMARY KEY, nm_mun TEXT NOT NULL, cd_uf TEXT NOT NULL) WITHOUT ROWID;
            CREATE TABLE setor (cd_setor TEXT PRIMARY KEY, cd_mun TEXT NOT NULL, situacao TEXT, area_km2 REAL, populacao INTEGER) WITHOUT ROWID;
            CREATE TABLE demografia (cd_setor TEXT PRIMARY KEY, moradores INTEGER, homens INTEGER, mulheres INTEGER) WITHOUT ROWID;
        SQL);
        $db->exec($inserts);

        $this->prepararBaseCenso();

        if ($usarComoConexao) {
            config(['database.connections.sqlite.database' => $arquivo]);
            DB::purge('sqlite');
        }

        return $arquivo;
    }

    protected function prepararBaseCenso(): void
    {
        self::assertSame(0, Artisan::call('censo:preparar', [
            '--database' => $this->arquivoBaseCenso,
            '--skip-validation' => true,
        ]));
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
