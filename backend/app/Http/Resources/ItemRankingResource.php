<?php

namespace App\Http\Resources;

use App\Dados\ItemRanking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property ItemRanking $resource */
final class ItemRankingResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $item = $this->resource;

        return [
            'posicao' => $item->posicao,
            'codigo' => $item->codigo,
            'nome' => $item->nome,
            'populacao' => $item->populacao,
            'area_km2' => round($item->areaKm2, 2),
            'densidade_hab_km2' => $item->densidade === null ? null : round($item->densidade, 2),
        ];
    }
}
