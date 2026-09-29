<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\Concerns\UsaBaseCenso;
use Tests\Concerns\ValidaContratoOpenApi;
use Tests\TestCase;

final class MunicipiosApiTest extends TestCase
{
    use UsaBaseCenso;
    use ValidaContratoOpenApi;

    private const FIXTURE = <<<'SQL'
        INSERT INTO uf VALUES ('35', 'São Paulo'), ('14', 'Roraima'), ('43', 'Rio Grande do Sul');
        INSERT INTO municipio VALUES
            ('3550308', 'São Paulo', '35'),
            ('3548500', 'Santos', '35'),
            ('3500010', 'Santos Dumont', '35'),
            ('3500011', 'Brejo dos Santos', '35'),
            ('3500012', 'São Paulo das Missões', '35'),
            ('1400001', 'Bom Jesus', '14'),
            ('4300001', 'Bom Jesus', '43'),
            ('1400002', 'Espigão D''Oeste', '14'),
            ('.', '', '43');
        INSERT INTO setor VALUES
            ('s01', '3550308', 'Urbana', 1, 1000),
            ('s02', '3550308', 'Rural', 1, 10),
            ('s03', '3550308', NULL, 1, 0),
            ('s04', '3548500', 'Urbana', 1, 400),
            ('s05', '3500010', 'Urbana', 1, 50),
            ('s06', '3500011', 'Urbana', 1, 900),
            ('s07', '3500012', 'Rural', 1, 10),
            ('s08', '1400001', 'Rural', 1, 10),
            ('s09', '4300001', 'Rural', 1, 20),
            ('s10', '1400002', 'Urbana', 1, 5),
            ('s11', '.', 'Rural', 100, 0);
        INSERT INTO demografia VALUES
            ('s01', 1000, 480, 500),
            ('s02', 10, 5, 5);
        SQL;

    protected function setUp(): void
    {
        parent::setUp();

        $this->criarBaseCenso(self::FIXTURE);
    }

    public function test_busca_sem_acento_encontra_nome_acentuado_e_respeita_o_contrato(): void
    {
        $response = $this->getJson('/api/v1/municipios?q=sao paulo');

        $response->assertOk()
            ->assertJsonPath('data.0.codigo', '3550308')
            ->assertJsonPath('data.0.rotulo', 'São Paulo - SP')
            ->assertJsonPath('data.0.uf.sigla', 'SP');
        $this->validarContrato($response, '/api/v1/municipios');
    }

    public function test_palavras_em_qualquer_ordem(): void
    {
        $this->getJson('/api/v1/municipios?q=paulo sao')->assertJsonPath('data.0.codigo', '3550308');
    }

    public function test_homonimos_sao_identificados_pela_uf(): void
    {
        $rotulos = $this->getJson('/api/v1/municipios?q=bom jesus')->json('data.*.rotulo');

        self::assertEqualsCanonicalizing(['Bom Jesus - RR', 'Bom Jesus - RS'], $rotulos);
    }

    public function test_apostrofo_e_termo_de_duas_letras(): void
    {
        $this->getJson("/api/v1/municipios?q=espigao d'oeste")->assertJsonPath('data.0.codigo', '1400002');
        $this->getJson('/api/v1/municipios?q=sp')->assertJsonPath('data.0.codigo', '1400002');
    }

    public function test_relevancia_exato_prefixo_e_depois_contem(): void
    {
        $nomes = $this->getJson('/api/v1/municipios?q=santos')->json('data.*.nome');

        // Brejo dos Santos tem mais população, mas só "contém" o termo.
        self::assertSame(['Santos', 'Santos Dumont', 'Brejo dos Santos'], $nomes);
    }

    public function test_sintaxe_de_busca_digitada_nao_quebra(): void
    {
        $this->getJson('/api/v1/municipios?q=sao* OR -paulo')->assertOk();
        $this->getJson('/api/v1/municipios?q=xyzxyz')->assertOk()->assertExactJson(['data' => []]);
    }

    public function test_validacao_de_parametros(): void
    {
        $curto = $this->getJson('/api/v1/municipios?q=s');
        $curto->assertStatus(422)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonStructure(['errors' => ['q']]);
        $this->validarContrato($curto, '/api/v1/municipios');

        $this->getJson('/api/v1/municipios?q=santos&limite=50')->assertStatus(422)->assertJsonStructure(['errors' => ['limite']]);
        self::assertCount(2, $this->getJson('/api/v1/municipios?q=santos&limite=2')->json('data'));
    }

    public function test_registro_extra_nunca_aparece(): void
    {
        self::assertNotContains('.', $this->getJson('/api/v1/municipios?q=bom')->json('data.*.codigo'));
        $this->getJson('/api/v1/municipios/.')->assertNotFound();
    }

    public function test_resumo_do_municipio(): void
    {
        $response = $this->getJson('/api/v1/municipios/3550308');

        $response->assertOk()
            ->assertJsonPath('data.populacao', 1010)
            ->assertJsonPath('data.setores', ['total' => 3, 'urbanos' => 1, 'rurais' => 1, 'sem_classificacao' => 1])
            ->assertJsonPath('data.sexo.nao_informado', 20)
            ->assertJsonPath('data.sexo.percentual_homens', 48.99);
        $this->validarContrato($response, '/api/v1/municipios/{codigo}');
    }

    public function test_resumo_inexistente_ou_mal_formado(): void
    {
        $inexistente = $this->getJson('/api/v1/municipios/9999999');
        $inexistente->assertNotFound()
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('detail', 'Município não encontrado.');
        self::assertStringNotContainsString('public', (string) $inexistente->headers->get('Cache-Control'));
        $this->validarContrato($inexistente, '/api/v1/municipios/{codigo}');

        $this->getJson('/api/v1/municipios/abc')->assertNotFound()->assertJsonPath('detail', 'Recurso não encontrado.');
    }
}
