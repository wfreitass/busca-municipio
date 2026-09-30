<?php

namespace App\Http\Resources;

use App\Dados\UfRef;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property UfRef $resource */
final class UfRefResource extends JsonResource
{
    /** @return array<string, string> */
    public function toArray(Request $request): array
    {
        return [
            'codigo' => $this->resource->codigo,
            'sigla' => $this->resource->sigla,
            'nome' => $this->resource->nome,
        ];
    }
}
