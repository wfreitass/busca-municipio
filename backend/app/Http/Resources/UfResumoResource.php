<?php

namespace App\Http\Resources;

use App\Models\Uf;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Uf */
class UfResumoResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'codigo' => $this->cd_uf,
            'sigla' => $this->sigla,
            'nome' => $this->nm_uf,
            'populacao' => $this->populacao,
            'area_km2' => round($this->area_km2, 2),
            'densidade_hab_km2' => $this->densidade === null ? null : round($this->densidade, 2),
            'total_municipios' => $this->total_municipios,
        ];
    }
}
