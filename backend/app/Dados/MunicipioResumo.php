<?php

declare(strict_types=1);

namespace App\Dados;

final readonly class MunicipioResumo
{
    public function __construct(
        public string $codigo,
        public string $nome,
        public UfRef $uf,
        public int $populacao,
        public float $areaKm2,
        public ?float $densidade,
        public int $setoresTotal,
        public int $setoresUrbanos,
        public int $setoresRurais,
        public int $setoresSemClassificacao,
        public int $homens,
        public int $mulheres,
        public int $sexoNaoInformado,
    ) {}
}
