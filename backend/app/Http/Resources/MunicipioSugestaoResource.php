<?php

namespace App\Http\Resources;

use App\Dados\MunicipioSugestao;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property MunicipioSugestao $resource */
final class MunicipioSugestaoResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'codigo' => $this->resource->codigo,
            'nome' => $this->resource->nome,
            'uf' => new UfRefResource($this->resource->uf),
            'rotulo' => $this->resource->rotulo(),
        ];
    }
}
