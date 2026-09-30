<?php

namespace App\Http\Controllers\Api\V1;

use App\Dados\UfResumo;
use App\Http\Controllers\Controller;
use App\Http\Requests\RankingUfRequest;
use App\Http\Resources\ItemRankingResource;
use App\Http\Resources\UfRefResource;
use App\Http\Resources\UfResumoResource;
use App\Queries\UfQuery;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class UfController extends Controller
{
    public function __construct(private readonly UfQuery $query) {}

    public function listar(): AnonymousResourceCollection
    {
        return UfRefResource::collection($this->query->listar());
    }

    public function mostrar(string $sigla): UfResumoResource
    {
        return new UfResumoResource($this->ufOu404($sigla));
    }

    public function ranking(string $sigla, RankingUfRequest $request): AnonymousResourceCollection
    {
        $pagina = $this->query->ranking($this->ufOu404($sigla), $request->pagina(), $request->porPagina());

        return ItemRankingResource::collection($pagina->itens)->additional(['meta' => [
            'pagina' => $pagina->pagina,
            'por_pagina' => $pagina->porPagina,
            'total' => $pagina->total,
            'total_paginas' => $pagina->totalPaginas(),
        ]]);
    }

    private function ufOu404(string $sigla): UfResumo
    {
        return $this->query->resumo($sigla) ?? abort(404, 'UF não encontrada.');
    }
}
