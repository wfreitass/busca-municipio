<?php

declare(strict_types=1);

namespace App\Censo;

use PDO;
use PDOStatement;
use RuntimeException;

final class PreparadorBaseCenso
{
    public function preparar(PDO $db, bool $validarTotais = true): void
    {
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->beginTransaction();

        try {
            $db->exec('CREATE INDEX IF NOT EXISTS ix_setor_mun ON setor(cd_mun)');
            $db->exec('DROP TABLE IF EXISTS municipio_busca');
            $db->exec('DROP TABLE IF EXISTS uf_resumo');
            $db->exec('DROP TABLE IF EXISTS municipio_resumo');

            $db->exec(<<<'SQL'
                CREATE TABLE municipio_resumo (
                    cd_mun TEXT PRIMARY KEY,
                    nm_mun TEXT NOT NULL,
                    nm_busca TEXT NOT NULL,
                    cd_uf TEXT NOT NULL,
                    sigla_uf TEXT NOT NULL,
                    consultavel INTEGER NOT NULL DEFAULT 1,
                    populacao INTEGER NOT NULL,
                    area_km2 REAL NOT NULL,
                    densidade REAL,
                    setores_total INTEGER NOT NULL,
                    setores_urbanos INTEGER NOT NULL,
                    setores_rurais INTEGER NOT NULL,
                    setores_sem_classificacao INTEGER NOT NULL,
                    homens INTEGER NOT NULL,
                    mulheres INTEGER NOT NULL,
                    sexo_nao_informado INTEGER NOT NULL,
                    posicao_densidade_uf INTEGER
                ) WITHOUT ROWID
            SQL);

            $db->exec(<<<'SQL'
                INSERT INTO municipio_resumo (
                    cd_mun, nm_mun, nm_busca, cd_uf, sigla_uf, consultavel,
                    populacao, area_km2, densidade, setores_total,
                    setores_urbanos, setores_rurais, setores_sem_classificacao,
                    homens, mulheres, sexo_nao_informado, posicao_densidade_uf
                )
                SELECT cd_mun, nm_mun, '', cd_uf,
                       '', CASE WHEN cd_mun = '.' THEN 0 ELSE 1 END,
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

            $update = $db->prepare(
                'UPDATE municipio_resumo SET nm_busca = :nm_busca, sigla_uf = :sigla_uf WHERE cd_mun = :cd_mun'
            );
            // Lê tudo antes de atualizar: iterar um cursor aberto na mesma tabela que recebe UPDATE é instável no SQLite.
            $municipios = $this->query($db, 'SELECT cd_mun, nm_mun, cd_uf FROM municipio_resumo')->fetchAll(PDO::FETCH_ASSOC);
            foreach ($municipios as $municipio) {
                $update->execute([
                    'cd_mun' => $municipio['cd_mun'],
                    'nm_busca' => NormalizadorTexto::normalizar($municipio['nm_mun']),
                    'sigla_uf' => SiglasUf::sigla($municipio['cd_uf']),
                ]);
            }

            $db->exec(<<<'SQL'
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

            $db->exec('CREATE INDEX ix_mr_busca ON municipio_resumo(consultavel, nm_busca)');
            $db->exec('CREATE INDEX ix_mr_ranking ON municipio_resumo(cd_uf, posicao_densidade_uf)');
            $db->exec("CREATE VIRTUAL TABLE municipio_busca USING fts5(cd_mun UNINDEXED, nm_busca, tokenize='trigram')");
            $db->exec('INSERT INTO municipio_busca (cd_mun, nm_busca) SELECT cd_mun, nm_busca FROM municipio_resumo WHERE consultavel = 1');

            $db->exec(<<<'SQL'
                CREATE TABLE uf_resumo (
                    cd_uf TEXT PRIMARY KEY,
                    sigla TEXT NOT NULL UNIQUE,
                    nm_uf TEXT NOT NULL,
                    populacao INTEGER NOT NULL,
                    area_km2 REAL NOT NULL,
                    densidade REAL,
                    total_municipios INTEGER NOT NULL
                ) WITHOUT ROWID
            SQL);
            $db->exec(<<<'SQL'
                INSERT INTO uf_resumo (cd_uf, sigla, nm_uf, populacao, area_km2, densidade, total_municipios)
                SELECT r.cd_uf, r.sigla_uf, u.nm_uf,
                       SUM(r.populacao), SUM(r.area_km2),
                       CASE WHEN SUM(r.area_km2) > 0 THEN CAST(SUM(r.populacao) AS REAL) / SUM(r.area_km2) END,
                       SUM(r.consultavel)
                FROM municipio_resumo r
                JOIN uf u ON u.cd_uf = r.cd_uf
                GROUP BY r.cd_uf, r.sigla_uf, u.nm_uf
            SQL);

            $totais = $this->query(
                $db,
                'SELECT COUNT(*) AS ufs, '
                .'SUM(populacao) AS populacao, SUM(area_km2) AS area '
                .'FROM uf_resumo'
            )->fetch(PDO::FETCH_ASSOC);
            if (! is_array($totais)) {
                throw new RuntimeException('Não foi possível ler os totais do read model.');
            }
            $consultaveis = (int) $this->query(
                $db,
                'SELECT COUNT(*) FROM municipio_resumo WHERE consultavel = 1'
            )->fetchColumn();

            if ($validarTotais && ((int) $totais['ufs'] !== 27
                || $consultaveis !== 5570
                || (int) $totais['populacao'] !== 203080756
                || abs((float) $totais['area'] - 8510417) > 1)) {
                throw new RuntimeException(sprintf(
                    'Totais inválidos: UFs=%s, municípios=%d, população=%s, área=%s.',
                    $totais['ufs'],
                    $consultaveis,
                    $totais['populacao'],
                    $totais['area'],
                ));
            }

            $db->commit();
        } catch (\Throwable $exception) {
            $db->rollBack();
            throw $exception;
        }
    }

    private function query(PDO $db, string $sql): PDOStatement
    {
        $statement = $db->query($sql);

        if ($statement === false) {
            throw new RuntimeException('Consulta SQLite falhou.');
        }

        return $statement;
    }
}
