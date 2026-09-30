<?php

namespace App\Http\Resources;

use App\Models\Municipio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Municipio */
class ItemRankingResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'posicao' => $this->posicao_densidade_uf,
            'codigo' => $this->cd_mun,
            'nome' => $this->nm_mun,
            'populacao' => $this->populacao,
            'area_km2' => round($this->area_km2, 2),
            'densidade_hab_km2' => $this->densidade === null ? null : round($this->densidade, 2),
        ];
    }
}
