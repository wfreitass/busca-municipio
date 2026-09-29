<?php

declare(strict_types=1);

namespace App\Dados;

final readonly class UfRef
{
    public function __construct(
        public string $codigo,
        public string $sigla,
        public string $nome,
    ) {}
}
