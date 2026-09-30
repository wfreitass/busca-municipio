<?php

namespace App\Http\Resources;

use App\Models\Municipio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Municipio */
class MunicipioResumoResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $comSexo = $this->homens + $this->mulheres;

        return [
            'codigo' => $this->cd_mun,
            'nome' => $this->nm_mun,
            'uf' => new UfRefResource($this->uf),
            'populacao' => $this->populacao,
            'area_km2' => round($this->area_km2, 2),
            'densidade_hab_km2' => $this->densidade === null ? null : round($this->densidade, 2),
            'setores' => [
                'total' => $this->setores_total,
                'urbanos' => $this->setores_urbanos,
                'rurais' => $this->setores_rurais,
                'sem_classificacao' => $this->setores_sem_classificacao,
            ],
            'sexo' => [
                'homens' => $this->homens,
                'mulheres' => $this->mulheres,
                'nao_informado' => $this->sexo_nao_informado,
                // Percentuais sobre homens + mulheres (a parte "não informada" fica fora da base).
                'percentual_homens' => $comSexo > 0 ? round($this->homens / $comSexo * 100, 2) : null,
                'percentual_mulheres' => $comSexo > 0 ? round($this->mulheres / $comSexo * 100, 2) : null,
            ],
        ];
    }
}
