<?php

namespace Tests\Feature;

use PDO;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('dados')]
final class TotaisOficiaisCensoTest extends TestCase
{
    public function test_read_model_real_confere_com_os_totais_do_ibge(): void
    {
        $arquivo = database_path('censo.sqlite');
        if (! is_file($arquivo)) {
            self::markTestSkipped('Base preparada ausente: rode censo:preparar (a imagem Docker já faz isso no build).');
        }

        $db = new PDO('sqlite:'.$arquivo);
        $totaisStatement = $db->query('SELECT COUNT(*) AS ufs, SUM(populacao) AS populacao, SUM(area_km2) AS area FROM uf_resumo');
        $municipiosStatement = $db->query('SELECT COUNT(*) FROM municipio_resumo WHERE consultavel = 1');
        self::assertNotFalse($totaisStatement);
        self::assertNotFalse($municipiosStatement);
        $totais = $totaisStatement->fetch(PDO::FETCH_ASSOC);
        self::assertIsArray($totais);

        self::assertSame(27, (int) $totais['ufs']);
        self::assertSame(203080756, (int) $totais['populacao']);
        self::assertEqualsWithDelta(8510417, (float) $totais['area'], 1);
        self::assertSame(5570, (int) $municipiosStatement->fetchColumn());
    }
}
