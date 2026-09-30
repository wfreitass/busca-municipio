<?php

namespace App\Busca;

use App\Dados\MunicipioSugestao;

/**
 * Porta da busca de municípios por nome. Única abstração com variação prevista:
 * hoje FTS5 no SQLite; amanhã, por exemplo, um adaptador Meilisearch.
 */
interface MunicipioSearch
{
    /** @return list<MunicipioSugestao> */
    public function buscar(string $termo, int $limite): array;
}
