<?php

namespace App\Dados;

final readonly class UfResumo
{
    public function __construct(
        public UfRef $uf,
        public int $populacao,
        public float $areaKm2,
        public ?float $densidade,
        public int $totalMunicipios,
    ) {}
}
