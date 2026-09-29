<?php

declare(strict_types=1);

namespace App\Dados;

final readonly class ItemRanking
{
    public function __construct(
        public int $posicao,
        public string $codigo,
        public string $nome,
        public int $populacao,
        public float $areaKm2,
        public ?float $densidade,
    ) {}
}
