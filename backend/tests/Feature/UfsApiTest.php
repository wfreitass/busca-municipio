<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\Concerns\UsaBaseCenso;
use Tests\Concerns\ValidaContratoOpenApi;
use Tests\TestCase;

final class UfsApiTest extends TestCase
{
    use UsaBaseCenso;
    use ValidaContratoOpenApi;

    // SP com 7 municípios (densidades 700, 600, …, 100) + um de área zero; RR com 1 + registro extra.
    private const FIXTURE = <<<'SQL'
        INSERT INTO uf VALUES ('35', 'São Paulo'), ('14', 'Roraima'), ('12', 'Acre');
        INSERT INTO municipio VALUES
            ('3500001', 'Alfa', '35'), ('3500002', 'Beta', '35'), ('3500003', 'Gama', '35'),
            ('3500004', 'Delta', '35'), ('3500005', 'Épsilon', '35'), ('3500006', 'Zeta', '35'),
            ('3500007', 'Eta', '35'), ('3500008', 'Sem Área', '35'),
            ('1400001', 'Boa Vista', '14'), ('.', '', '14'),
            ('1200001', 'Rio Branco', '12');
        INSERT INTO setor VALUES
            ('s1', '3500001', 'Urbana', 1, 100), ('s2', '3500002', 'Urbana', 1, 200),
            ('s3', '3500003', 'Urbana', 1, 300), ('s4', '3500004', 'Urbana', 1, 400),
            ('s5', '3500005', 'Urbana', 1, 500), ('s6', '3500006', 'Urbana', 1, 600),
            ('s7', '3500007', 'Urbana', 1, 700), ('s8', '3500008', 'Urbana', 0, 50),
            ('s9', '1400001', 'Urbana', 10, 100), ('s10', '.', 'Rural', 90, 0),
            ('s11', '1200001', 'Urbana', 5, 50);
        SQL;

    protected function setUp(): void
    {
        parent::setUp();

        $this->criarBaseCenso(self::FIXTURE);
    }

    public function test_lista_ufs_ordenadas_por_nome(): void
    {
        $response = $this->getJson('/api/v1/ufs');

        $response->assertOk();
        self::assertSame(['Acre', 'Roraima', 'São Paulo'], $response->json('data.*.nome'));
        self::assertSame(['AC', 'RR', 'SP'], $response->json('data.*.sigla'));
    }

    public function test_resumo_da_uf_com_densidade_ponderada_e_sigla_minuscula(): void
    {
        $response = $this->getJson('/api/v1/ufs/sp');

        $response->assertOk()
            ->assertJsonPath('data.sigla', 'SP')
            ->assertJsonPath('data.populacao', 2850)
            ->assertJsonPath('data.total_municipios', 8)
            ->assertJsonPath('data.densidade_hab_km2', 407.14);
        $this->validarContrato($response, '/api/v1/ufs/{sigla}');

        // Registro extra soma área, mas não conta como município.
        $this->getJson('/api/v1/ufs/RR')->assertJsonPath('data.area_km2', 100)->assertJsonPath('data.total_municipios', 1);
    }

    public function test_uf_inexistente(): void
    {
        foreach (['/api/v1/ufs/XX', '/api/v1/ufs/XX/municipios'] as $uri) {
            $this->getJson($uri)
                ->assertNotFound()
                ->assertHeader('Content-Type', 'application/problem+json')
                ->assertJsonPath('detail', 'UF não encontrada.');
        }
    }

    public function test_ranking_decrescente_com_posicao_global_e_densidade_nula_no_fim(): void
    {
        $primeira = $this->getJson('/api/v1/ufs/SP/municipios?por_pagina=3');
        $primeira->assertOk()->assertJsonPath('meta', ['pagina' => 1, 'por_pagina' => 3, 'total' => 8, 'total_paginas' => 3]);
        self::assertSame(['Eta', 'Zeta', 'Épsilon'], $primeira->json('data.*.nome'));
        $this->validarContrato($primeira, '/api/v1/ufs/{sigla}/municipios');

        $segunda = $this->getJson('/api/v1/ufs/SP/municipios?pagina=2&por_pagina=3');
        self::assertSame([4, 5, 6], $segunda->json('data.*.posicao'));

        $ultima = $this->getJson('/api/v1/ufs/SP/municipios?pagina=3&por_pagina=3');
        self::assertSame(['Alfa', 'Sem Área'], $ultima->json('data.*.nome'));
        $ultima->assertJsonPath('data.1.densidade_hab_km2', null);
    }

    public function test_uf_pequena_e_pagina_alem_do_fim(): void
    {
        $this->getJson('/api/v1/ufs/RR/municipios')->assertJsonCount(1, 'data')->assertJsonPath('meta.total_paginas', 1);
        $this->getJson('/api/v1/ufs/RR/municipios?pagina=5')->assertOk()->assertJsonCount(0, 'data')->assertJsonPath('meta.total', 1);
    }

    public function test_parametros_invalidos(): void
    {
        foreach (['por_pagina=500' => 'por_pagina', 'pagina=0' => 'pagina', 'pagina=abc' => 'pagina'] as $query => $campo) {
            $response = $this->getJson("/api/v1/ufs/SP/municipios?{$query}");
            $response->assertStatus(422)->assertJsonStructure(['errors' => [$campo]]);
        }
    }
}
