<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RankingUfRequest;
use App\Http\Resources\ItemRankingResource;
use App\Http\Resources\UfRefResource;
use App\Http\Resources\UfResumoResource;
use App\Models\Uf;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UfController extends Controller
{
    public function listar(): AnonymousResourceCollection
    {
        return UfRefResource::collection(Uf::query()->orderBy('nm_uf')->get());
    }

    public function mostrar(Uf $uf): UfResumoResource
    {
        return new UfResumoResource($uf);
    }

    public function ranking(Uf $uf, RankingUfRequest $request): AnonymousResourceCollection
    {
        $pagina = $request->pagina();
        $porPagina = $request->porPagina();

        // forPage em vez de paginate(): mantém o `meta` do contrato e evita um COUNT(*) —
        // o total já está pré-calculado em uf_resumo.
        $municipios = $uf->municipios()->consultaveis()->rankingDensidade()->forPage($pagina, $porPagina)->get();

        return ItemRankingResource::collection($municipios)->additional(['meta' => [
            'pagina' => $pagina,
            'por_pagina' => $porPagina,
            'total' => $uf->total_municipios,
            'total_paginas' => (int) ceil($uf->total_municipios / $porPagina),
        ]]);
    }
}
