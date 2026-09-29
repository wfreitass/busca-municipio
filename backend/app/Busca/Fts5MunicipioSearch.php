<?php

declare(strict_types=1);

namespace App\Busca;

use App\Censo\NormalizadorTexto;
use App\Dados\MunicipioSugestao;
use App\Dados\UfRef;
use Illuminate\Support\Facades\DB;
use stdClass;

final class Fts5MunicipioSearch implements MunicipioSearch
{
    /** O tokenizador trigram só indexa trechos de 3 letras ou mais. */
    private const TAMANHO_MINIMO_TRIGRAMA = 3;

    public function buscar(string $termo, int $limite): array
    {
        // Após normalizar, só letras, dígitos e espaço sobrevivem: nada de sintaxe FTS nem curingas de LIKE.
        $termo = trim((string) preg_replace('/[^a-z0-9 ]+/', ' ', NormalizadorTexto::normalizar($termo)));
        $termo = (string) preg_replace('/\s+/', ' ', $termo);
        if ($termo === '') {
            return [];
        }

        $palavras = explode(' ', $termo);
        $longas = array_values(array_filter($palavras, fn (string $p): bool => strlen($p) >= self::TAMANHO_MINIMO_TRIGRAMA));
        $curtas = array_values(array_filter($palavras, fn (string $p): bool => strlen($p) < self::TAMANHO_MINIMO_TRIGRAMA));

        $from = 'municipio_resumo mr';
        $where = ['mr.consultavel = 1'];
        $bindings = [];

        if ($longas !== []) {
            // Cada palavra entre aspas: AND entre elas, em qualquer ordem.
            $from = 'municipio_busca JOIN municipio_resumo mr ON mr.cd_mun = municipio_busca.cd_mun';
            $where[] = 'municipio_busca MATCH ?';
            $bindings[] = implode(' ', array_map(fn (string $p): string => '"'.$p.'"', $longas));
        }

        foreach ($curtas as $curta) {
            $where[] = 'mr.nm_busca LIKE ?';
            $bindings[] = '%'.$curta.'%';
        }

        // Relevância explícita (o bm25 não prioriza o nome exato): igual > prefixo > contém, depois população.
        $sql = 'SELECT mr.cd_mun, mr.nm_mun, u.cd_uf, u.sigla, u.nm_uf'
            ." FROM {$from} JOIN uf_resumo u ON u.cd_uf = mr.cd_uf"
            .' WHERE '.implode(' AND ', $where)
            .' ORDER BY CASE WHEN mr.nm_busca = ? THEN 0 WHEN mr.nm_busca LIKE ? THEN 1 ELSE 2 END,'
            .' mr.populacao DESC, mr.nm_busca'
            .' LIMIT ?';
        array_push($bindings, $termo, $termo.'%', $limite);

        /** @var list<stdClass> $linhas */
        $linhas = DB::select($sql, $bindings);

        return array_map(
            fn (stdClass $linha): MunicipioSugestao => new MunicipioSugestao(
                (string) $linha->cd_mun,
                (string) $linha->nm_mun,
                new UfRef((string) $linha->cd_uf, (string) $linha->sigla, (string) $linha->nm_uf),
            ),
            $linhas,
        );
    }
}
