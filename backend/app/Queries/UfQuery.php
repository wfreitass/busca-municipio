<?php

declare(strict_types=1);

namespace App\Queries;

use App\Dados\ItemRanking;
use App\Dados\Pagina;
use App\Dados\UfRef;
use App\Dados\UfResumo;
use Illuminate\Support\Facades\DB;
use stdClass;

final class UfQuery
{
    /** @return list<UfRef> */
    public function listar(): array
    {
        return array_values(DB::table('uf_resumo')
            ->orderBy('nm_uf')
            ->get(['cd_uf', 'sigla', 'nm_uf'])
            ->map(fn (stdClass $uf): UfRef => new UfRef((string) $uf->cd_uf, (string) $uf->sigla, (string) $uf->nm_uf))
            ->all());
    }

    public function resumo(string $sigla): ?UfResumo
    {
        /** @var stdClass|null $uf */
        $uf = DB::table('uf_resumo')->where('sigla', strtoupper($sigla))->first();
        if ($uf === null) {
            return null;
        }

        return new UfResumo(
            uf: new UfRef((string) $uf->cd_uf, (string) $uf->sigla, (string) $uf->nm_uf),
            populacao: (int) $uf->populacao,
            areaKm2: (float) $uf->area_km2,
            densidade: $uf->densidade === null ? null : (float) $uf->densidade,
            totalMunicipios: (int) $uf->total_municipios,
        );
    }

    /**
     * Página do ranking de densidade, lida pelo índice (cd_uf, posicao_densidade_uf) pré-calculado no build.
     *
     * @return Pagina<ItemRanking>
     */
    public function ranking(UfResumo $uf, int $pagina, int $porPagina): Pagina
    {
        $itens = array_values(DB::table('municipio_resumo')
            ->where('cd_uf', $uf->uf->codigo)
            ->where('consultavel', 1)
            ->orderBy('posicao_densidade_uf')
            ->offset(($pagina - 1) * $porPagina)
            ->limit($porPagina)
            ->get(['posicao_densidade_uf', 'cd_mun', 'nm_mun', 'populacao', 'area_km2', 'densidade'])
            ->map(fn (stdClass $m): ItemRanking => new ItemRanking(
                posicao: (int) $m->posicao_densidade_uf,
                codigo: (string) $m->cd_mun,
                nome: (string) $m->nm_mun,
                populacao: (int) $m->populacao,
                areaKm2: (float) $m->area_km2,
                densidade: $m->densidade === null ? null : (float) $m->densidade,
            ))
            ->all());

        return new Pagina($itens, $pagina, $porPagina, $uf->totalMunicipios);
    }
}
