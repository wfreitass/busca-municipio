<?php

namespace App\Busca;

use App\Models\Municipio;
use Illuminate\Database\Eloquent\Collection;

/**
 * Busca de municípios por nome. Única interface do back-end, porque é o único ponto
 * com troca de motor prevista: hoje FTS5 no SQLite; amanhã, por exemplo, Meilisearch.
 */
interface BuscaMunicipios
{
    /** @return Collection<int, Municipio> municípios consultáveis, com a UF carregada, em ordem de relevância */
    public function buscar(string $termo, int $limite): Collection;
}
