<?php

namespace Tests\Feature;

use App\Models\Municipio;
use App\Models\Uf;
use Illuminate\Support\Facades\DB;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->criarBaseCenso(self::FIXTURE);
    }

    public function test_agrega_populacao_area_setores_e_sexo(): void
    {
        $agua = Municipio::query()->findOrFail('3500001');

        self::assertSame(120, $agua->populacao);
        self::assertEqualsWithDelta(10.0, $agua->area_km2, 0.0001);
        self::assertEqualsWithDelta(12.0, $agua->densidade, 0.0001);
        self::assertSame([3, 1, 1, 1], [
            $agua->setores_total, $agua->setores_urbanos, $agua->setores_rurais, $agua->setores_sem_classificacao,
        ]);
        // Setor sem demografia não some: sua população vira "não informado".
        self::assertSame([40, 50, 30], [$agua->homens, $agua->mulheres, $agua->sexo_nao_informado]);
        self::assertSame('agua alta', $agua->nm_busca);
        self::assertSame('SP', $agua->sigla_uf);
    }

    public function test_municipio_sem_area_tem_densidade_nula_e_fica_no_fim_do_ranking(): void
    {
        $semSetores = Municipio::query()->findOrFail('3500003');

        self::assertNull($semSetores->densidade);
        self::assertSame(0, $semSetores->setores_total);
        self::assertSame([1 => 'Beta', 2 => 'Água Alta', 3 => 'Sem Setores'], $this->ranking('35'));
    }

    public function test_densidade_da_uf_e_ponderada_e_registro_extra_so_soma_area(): void
    {
        $sp = Uf::query()->findOrFail('35');
        self::assertSame(1120, $sp->populacao);
        self::assertEqualsWithDelta(1120 / 11, $sp->densidade, 0.0001);
        self::assertSame(3, $sp->total_municipios);

        $rr = Uf::query()->findOrFail('14');
        self::assertEqualsWithDelta(102.0, $rr->area_km2, 0.0001);
        self::assertSame(1, $rr->total_municipios);
        self::assertSame([1 => 'Gama'], $this->ranking('14'));
    }

    public function test_indice_de_busca_exclui_nao_consultaveis(): void
    {
        self::assertSame(4, DB::table('municipio_busca')->count());
        self::assertSame(0, DB::table('municipio_busca')->where('cd_mun', '.')->count());
    }

    public function test_reexecucao_e_idempotente(): void
    {
        $antes = Municipio::query()->orderBy('cd_mun')->get()->toArray();

        $this->prepararBaseCenso();

        self::assertSame($antes, Municipio::query()->orderBy('cd_mun')->get()->toArray());
    }

    /** @return array<int, string> */
    private function ranking(string $uf): array
    {
        return Municipio::query()
            ->where('cd_uf', $uf)
            ->consultaveis()
            ->rankingDensidade()
            ->pluck('nm_mun', 'posicao_densidade_uf')
            ->all();
    }
}
