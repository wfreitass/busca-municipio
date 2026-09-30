<?php

namespace App\Http\Resources;

use App\Models\Municipio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Municipio */
class MunicipioSugestaoResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'codigo' => $this->cd_mun,
            'nome' => $this->nm_mun,
            'uf' => new UfRefResource($this->uf),
            'rotulo' => $this->rotulo(),
        ];
    }
}
