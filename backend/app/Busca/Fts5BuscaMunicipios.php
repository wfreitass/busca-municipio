<?php

namespace App\Busca;

use App\Models\Municipio;
use App\Support\NormalizadorTexto;
use Illuminate\Database\Eloquent\Collection;

class Fts5BuscaMunicipios implements BuscaMunicipios
{
    /** O tokenizador trigram só indexa trechos de 3 letras ou mais. */
    private const TAMANHO_MINIMO_TRIGRAMA = 3;

    public function buscar(string $termo, int $limite): Collection
    {
        // Após normalizar, só letras, dígitos e espaço sobrevivem: nada de sintaxe FTS nem curingas de LIKE.
        $termo = trim((string) preg_replace('/[^a-z0-9 ]+/', ' ', NormalizadorTexto::normalizar($termo)));
        $termo = (string) preg_replace('/\s+/', ' ', $termo);
        if ($termo === '') {
            return new Collection;
        }

        $palavras = explode(' ', $termo);
        $longas = array_filter($palavras, fn (string $p): bool => strlen($p) >= self::TAMANHO_MINIMO_TRIGRAMA);
        $curtas = array_filter($palavras, fn (string $p): bool => strlen($p) < self::TAMANHO_MINIMO_TRIGRAMA);

        $query = Municipio::query()
            ->select('municipio_resumo.*')
            ->with('uf')
            ->consultaveis();

        if ($longas !== []) {
            // Cada palavra entre aspas: AND entre elas, em qualquer ordem.
            $query->join('municipio_busca', 'municipio_busca.cd_mun', '=', 'municipio_resumo.cd_mun')
                ->whereRaw('municipio_busca MATCH ?', [implode(' ', array_map(fn (string $p): string => '"'.$p.'"', $longas))]);
        }

        foreach ($curtas as $curta) {
            $query->where('municipio_resumo.nm_busca', 'like', '%'.$curta.'%');
        }

        // Relevância explícita (o bm25 não prioriza o nome exato): igual > prefixo > contém, depois população.
        return $query
            ->orderByRaw(
                'CASE WHEN municipio_resumo.nm_busca = ? THEN 0 WHEN municipio_resumo.nm_busca LIKE ? THEN 1 ELSE 2 END',
                [$termo, $termo.'%'],
            )
            ->orderByDesc('municipio_resumo.populacao')
            ->orderBy('municipio_resumo.nm_busca')
            ->limit($limite)
            ->get();
    }
}
