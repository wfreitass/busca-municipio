<?php

namespace App\Http\Controllers\Api\V1;

use App\Busca\MunicipioSearch;
use App\Http\Controllers\Controller;
use App\Http\Requests\BuscarMunicipiosRequest;
use App\Http\Resources\MunicipioResumoResource;
use App\Http\Resources\MunicipioSugestaoResource;
use App\Queries\MunicipioQuery;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class MunicipioController extends Controller
{
    public function buscar(BuscarMunicipiosRequest $request, MunicipioSearch $busca): AnonymousResourceCollection
    {
        return MunicipioSugestaoResource::collection($busca->buscar($request->termo(), $request->limite()));
    }

    public function mostrar(string $codigo, MunicipioQuery $query): MunicipioResumoResource
    {
        $municipio = $query->detalhe($codigo) ?? abort(404, 'Município não encontrado.');

        return new MunicipioResumoResource($municipio);
    }
}
