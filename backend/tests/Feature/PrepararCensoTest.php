<?php

namespace Tests\Feature;

use PDO;
use PDOStatement;
use Tests\Concerns\UsaBaseCenso;
use Tests\TestCase;

final class PrepararCensoTest extends TestCase
{
    use UsaBaseCenso;

    private const FIXTURE = <<<'SQL'
        INSERT INTO uf VALUES ('35', 'São Paulo'), ('14', 'Roraima');
        INSERT INTO municipio VALUES
            ('3500001', 'Água Alta', '35'),
            ('3500002', 'Beta', '35'),
            ('3500003', 'Sem Setores', '35'),
            ('1400001', 'Gama', '14'),
            ('.', '', '14');
        INSERT INTO setor VALUES
            ('s1', '3500001', 'Urbana', 1, 100),
            ('s2', '3500001', 'Rural', 9, 20),
            ('s3', '3500001', NULL, 0, NULL),
            ('s4', '3500002', 'Urbana', 1, 1000),
            ('s5', '1400001', 'Urbana', 2, 10),
            ('s6', '.', 'Rural', 100, 0);
        INSERT INTO demografia VALUES
            ('s1', 100, 40, 50),
            ('s3', 0, 0, 0),
            ('s4', 1000, 500, 500),
            ('s5', 10, 5, 5);
        SQL;

    private PDO $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new PDO('sqlite:'.$this->criarBaseCenso(self::FIXTURE, usarComoConexao: false));
    }

    public function test_agrega_populacao_area_setores_e_sexo(): void
    {
        $agua = $this->municipio('3500001');

        self::assertSame(120, $agua['populacao']);
        self::assertEqualsWithDelta(10.0, $agua['area_km2'], 0.0001);
        self::assertEqualsWithDelta(12.0, $agua['densidade'], 0.0001);
        self::assertSame([3, 1, 1, 1], [
            $agua['setores_total'], $agua['setores_urbanos'], $agua['setores_rurais'], $agua['setores_sem_classificacao'],
        ]);
        // Setor sem demografia não some: sua população vira "não informado".
        self::assertSame([40, 50, 30], [$agua['homens'], $agua['mulheres'], $agua['sexo_nao_informado']]);
        self::assertSame('agua alta', $agua['nm_busca']);
        self::assertSame('SP', $agua['sigla_uf']);
    }

    public function test_municipio_sem_area_tem_densidade_nula_e_fica_no_fim_do_ranking(): void
    {
        $semSetores = $this->municipio('3500003');

        self::assertNull($semSetores['densidade']);
        self::assertSame(0, $semSetores['setores_total']);
        self::assertSame([1 => 'Beta', 2 => 'Água Alta', 3 => 'Sem Setores'], $this->ranking('35'));
    }

    public function test_densidade_da_uf_e_ponderada_e_registro_extra_so_soma_area(): void
    {
        $sp = $this->uf('35');
        self::assertSame(1120, $sp['populacao']);
        self::assertEqualsWithDelta(1120 / 11, $sp['densidade'], 0.0001);
        self::assertSame(3, $sp['total_municipios']);

        $rr = $this->uf('14');
        self::assertEqualsWithDelta(102.0, $rr['area_km2'], 0.0001);
        self::assertSame(1, $rr['total_municipios']);
        self::assertSame([1 => 'Gama'], $this->ranking('14'));
    }

    public function test_indice_de_busca_exclui_nao_consultaveis(): void
    {
        self::assertSame(4, (int) $this->consultar('SELECT COUNT(*) FROM municipio_busca')->fetchColumn());
        self::assertSame(0, (int) $this->consultar("SELECT COUNT(*) FROM municipio_busca WHERE cd_mun = '.'")->fetchColumn());
    }

    public function test_reexecucao_e_idempotente(): void
    {
        $antes = $this->consultar('SELECT * FROM municipio_resumo ORDER BY cd_mun')->fetchAll(PDO::FETCH_ASSOC);

        $this->prepararBaseCenso();

        self::assertSame($antes, $this->consultar('SELECT * FROM municipio_resumo ORDER BY cd_mun')->fetchAll(PDO::FETCH_ASSOC));
    }

    /** @return array<string, mixed> */
    private function municipio(string $codigo): array
    {
        return $this->linha('SELECT * FROM municipio_resumo WHERE cd_mun = ?', [$codigo]);
    }

    /** @return array<string, mixed> */
    private function uf(string $codigo): array
    {
        return $this->linha('SELECT * FROM uf_resumo WHERE cd_uf = ?', [$codigo]);
    }

    /** @return array<int, string> */
    private function ranking(string $uf): array
    {
        return $this->consultar(
            'SELECT posicao_densidade_uf, nm_mun FROM municipio_resumo WHERE cd_uf = ? AND consultavel = 1 ORDER BY posicao_densidade_uf',
            [$uf],
        )->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * @param  list<string>  $parametros
     * @return array<string, mixed>
     */
    private function linha(string $sql, array $parametros): array
    {
        $linha = $this->consultar($sql, $parametros)->fetch(PDO::FETCH_ASSOC);
        self::assertIsArray($linha);

        return $linha;
    }

    /** @param  list<string>  $parametros */
    private function consultar(string $sql, array $parametros = []): PDOStatement
    {
        $statement = $this->db->prepare($sql);
        self::assertInstanceOf(PDOStatement::class, $statement);
        $statement->execute($parametros);

        return $statement;
    }
}
