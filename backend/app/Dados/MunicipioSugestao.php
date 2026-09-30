<?php

namespace App\Dados;

final readonly class MunicipioSugestao
{
    public function __construct(
        public string $codigo,
        public string $nome,
        public UfRef $uf,
    ) {}

    public function rotulo(): string
    {
        return "{$this->nome} - {$this->uf->sigla}";
    }
}
