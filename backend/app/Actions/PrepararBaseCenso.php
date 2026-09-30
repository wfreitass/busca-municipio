<?php

namespace App\Actions;

use App\Models\Municipio;
use App\Models\Uf;
use App\Support\NormalizadorTexto;
use App\Support\SiglasUf;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Carrega o read model (schema da migration cria_read_model_do_censo) a partir das tabelas cruas.
 *
 * A agregação é SQL explícito (INSERT … SELECT e window function): são 468 mil setores,
 * e hidratar Models linha a linha seria ordens de grandeza mais lento. Idempotente.
 */
class PrepararBaseCenso
{
    public const TOTAIS_IBGE = [
        'ufs' => 27,
        'municipios' => 5570,
        'populacao' => 203080756,
        'area_km2' => 8510417,
    ];

    public function handle(bool $validarTotais = true): void
    {
        DB::transaction(function () use ($validarTotais): void {
            $this->limpar();
            $this->agregarMunicipios();
            $this->preencherNomesESiglas();
            $this->calcularRanking();
            $this->indexarBusca();
            $this->agregarUfs();

            if ($validarTotais) {
                $this->validarTotais();
            }
        });
    }

    private function limpar(): void
    {
        DB::table('municipio_busca')->delete();
        DB::table('uf_resumo')->delete();
        DB::table('municipio_resumo')->delete();
    }

    /**
     * Regras (spec dados-censo): LEFT JOIN para não perder setores sem demografia, nulos como zero,
     * densidade nula com área zero e o registro '.' (lagoas do RS) fora do que é consultável.
     */
    private function agregarMunicipios(): void
    {
        DB::statement(<<<'SQL'
            INSERT INTO municipio_resumo (
                cd_mun, nm_mun, nm_busca, cd_uf, sigla_uf, consultavel,
                populacao, area_km2, densidade, setores_total,
                setores_urbanos, setores_rurais, setores_sem_classificacao,
                homens, mulheres, sexo_nao_informado, posicao_densidade_uf
            )
            SELECT cd_mun, nm_mun, '', cd_uf, '', CASE WHEN cd_mun = '.' THEN 0 ELSE 1 END,
                   populacao, area_km2,
                   CASE WHEN area_km2 > 0 THEN CAST(populacao AS REAL) / area_km2 END,
                   setores_total, setores_urbanos, setores_rurais,
                   setores_total - setores_urbanos - setores_rurais,
                   homens, mulheres, MAX(populacao - homens - mulheres, 0), NULL
            FROM (
                SELECT m.cd_mun, m.nm_mun, m.cd_uf,
                       COALESCE(SUM(s.populacao), 0) AS populacao,
                       COALESCE(SUM(s.area_km2), 0) AS area_km2,
                       COUNT(s.cd_setor) AS setores_total,
                       COALESCE(SUM(CASE WHEN s.situacao = 'Urbana' THEN 1 ELSE 0 END), 0) AS setores_urbanos,
                       COALESCE(SUM(CASE WHEN s.situacao = 'Rural' THEN 1 ELSE 0 END), 0) AS setores_rurais,
                       COALESCE(SUM(d.homens), 0) AS homens,
                       COALESCE(SUM(d.mulheres), 0) AS mulheres
                FROM municipio m
                LEFT JOIN setor s ON s.cd_mun = m.cd_mun
                LEFT JOIN demografia d ON d.cd_setor = s.cd_setor
                GROUP BY m.cd_mun, m.nm_mun, m.cd_uf
            ) agregados
        SQL);
    }

    private function preencherNomesESiglas(): void
    {
        Municipio::query()->get(['cd_mun', 'nm_mun', 'cd_uf'])->each(function (Municipio $municipio): void {
            $municipio->forceFill([
                'nm_busca' => NormalizadorTexto::normalizar($municipio->nm_mun),
                'sigla_uf' => SiglasUf::sigla($municipio->cd_uf),
            ])->save();
        });
    }

    /** Posição 1 = mais denso; densidade nula no fim; empate pelo nome. */
    private function calcularRanking(): void
    {
        DB::statement(<<<'SQL'
            UPDATE municipio_resumo
            SET posicao_densidade_uf = ranking.posicao
            FROM (
                SELECT cd_mun,
                       ROW_NUMBER() OVER (
                           PARTITION BY cd_uf
                           ORDER BY densidade IS NULL, densidade DESC, nm_busca, cd_mun
                       ) AS posicao
                FROM municipio_resumo
                WHERE consultavel = 1
            ) ranking
            WHERE ranking.cd_mun = municipio_resumo.cd_mun
        SQL);
    }

    private function indexarBusca(): void
    {
        DB::statement('INSERT INTO municipio_busca (cd_mun, nm_busca) SELECT cd_mun, nm_busca FROM municipio_resumo WHERE consultavel = 1');
    }

    /** Densidade da UF = soma(pop) / soma(área); a área do registro '.' entra, mas ele não conta como município. */
    private function agregarUfs(): void
    {
        DB::statement(<<<'SQL'
            INSERT INTO uf_resumo (cd_uf, sigla, nm_uf, populacao, area_km2, densidade, total_municipios)
            SELECT r.cd_uf, r.sigla_uf, u.nm_uf,
                   SUM(r.populacao), SUM(r.area_km2),
                   CASE WHEN SUM(r.area_km2) > 0 THEN CAST(SUM(r.populacao) AS REAL) / SUM(r.area_km2) END,
                   SUM(r.consultavel)
            FROM municipio_resumo r
            JOIN uf u ON u.cd_uf = r.cd_uf
            GROUP BY r.cd_uf, r.sigla_uf, u.nm_uf
        SQL);
    }

    private function validarTotais(): void
    {
        $obtido = [
            'ufs' => Uf::query()->count(),
            'municipios' => Municipio::query()->consultaveis()->count(),
            'populacao' => (int) Uf::query()->sum('populacao'),
            'area_km2' => (float) Uf::query()->sum('area_km2'),
        ];

        $esperado = self::TOTAIS_IBGE;
        if ($obtido['ufs'] !== $esperado['ufs']
            || $obtido['municipios'] !== $esperado['municipios']
            || $obtido['populacao'] !== $esperado['populacao']
            || abs($obtido['area_km2'] - $esperado['area_km2']) > 1) {
            throw new RuntimeException('Totais do read model não conferem com o IBGE: '.json_encode($obtido));
        }
    }
}
