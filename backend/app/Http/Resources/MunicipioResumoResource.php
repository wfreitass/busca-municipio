<?php

namespace App\Http\Resources;

use App\Dados\MunicipioResumo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property MunicipioResumo $resource */
final class MunicipioResumoResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $m = $this->resource;
        $comSexo = $m->homens + $m->mulheres;

        return [
            'codigo' => $m->codigo,
            'nome' => $m->nome,
            'uf' => new UfRefResource($m->uf),
            'populacao' => $m->populacao,
            'area_km2' => round($m->areaKm2, 2),
            'densidade_hab_km2' => $m->densidade === null ? null : round($m->densidade, 2),
            'setores' => [
                'total' => $m->setoresTotal,
                'urbanos' => $m->setoresUrbanos,
                'rurais' => $m->setoresRurais,
                'sem_classificacao' => $m->setoresSemClassificacao,
            ],
            'sexo' => [
                'homens' => $m->homens,
                'mulheres' => $m->mulheres,
                'nao_informado' => $m->sexoNaoInformado,
                'percentual_homens' => $comSexo > 0 ? round($m->homens / $comSexo * 100, 2) : null,
                'percentual_mulheres' => $comSexo > 0 ? round($m->mulheres / $comSexo * 100, 2) : null,
            ],
        ];
    }
}
