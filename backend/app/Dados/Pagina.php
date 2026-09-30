<?php

namespace App\Dados;

/** @template T */
final readonly class Pagina
{
    /** @param list<T> $itens */
    public function __construct(
        public array $itens,
        public int $pagina,
        public int $porPagina,
        public int $total,
    ) {}

    public function totalPaginas(): int
    {
        return (int) ceil($this->total / $this->porPagina);
    }
}
