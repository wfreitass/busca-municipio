<?php

namespace Tests\Unit;

use App\Support\NormalizadorTexto;
use App\Support\SiglasUf;
use PHPUnit\Framework\TestCase;

final class CensoTest extends TestCase
{
    public function test_mapa_tem_27_siglas_unicas(): void
    {
        self::assertCount(27, SiglasUf::todas());
        self::assertCount(27, array_unique(SiglasUf::todas()));
    }

    public function test_sigla_e_codigo_sao_case_insensitive(): void
    {
        self::assertSame('35', SiglasUf::codigo('sp'));
        self::assertSame('SP', SiglasUf::sigla('35'));
    }

    public function test_normaliza_acento_apostrofo_hifen_e_espacos(): void
    {
        self::assertSame('olho d agua das flores', NormalizadorTexto::normalizar("Olho-d'Água das Flores"));
        self::assertSame('sao paulo', NormalizadorTexto::normalizar('  São   Paulo '));
    }
}
