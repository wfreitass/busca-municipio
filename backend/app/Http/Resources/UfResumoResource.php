<?php

namespace App\Http\Resources;

use App\Dados\UfResumo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property UfResumo $resource */
final class UfResumoResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $uf = $this->resource;

        return [
            'codigo' => $uf->uf->codigo,
            'sigla' => $uf->uf->sigla,
            'nome' => $uf->uf->nome,
            'populacao' => $uf->populacao,
            'area_km2' => round($uf->areaKm2, 2),
            'densidade_hab_km2' => $uf->densidade === null ? null : round($uf->densidade, 2),
            'total_municipios' => $uf->totalMunicipios,
        ];
    }
}
