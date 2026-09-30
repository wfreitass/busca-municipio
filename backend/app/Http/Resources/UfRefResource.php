<?php

namespace App\Http\Resources;

use App\Models\Uf;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Uf */
class UfRefResource extends JsonResource
{
    /** @return array<string, string> */
    public function toArray(Request $request): array
    {
        return [
            'codigo' => $this->cd_uf,
            'sigla' => $this->sigla,
            'nome' => $this->nm_uf,
        ];
    }
}
