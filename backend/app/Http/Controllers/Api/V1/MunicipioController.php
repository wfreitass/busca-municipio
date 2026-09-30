<?php

namespace App\Http\Controllers\Api\V1;

use App\Busca\BuscaMunicipios;
use App\Http\Controllers\Controller;
use App\Http\Requests\BuscarMunicipiosRequest;
use App\Http\Resources\MunicipioResumoResource;
use App\Http\Resources\MunicipioSugestaoResource;
use App\Models\Municipio;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MunicipioController extends Controller
{
    public function buscar(BuscarMunicipiosRequest $request, BuscaMunicipios $busca): AnonymousResourceCollection
    {
        return MunicipioSugestaoResource::collection($busca->buscar($request->termo(), $request->limite()));
    }

    public function mostrar(Municipio $municipio): MunicipioResumoResource
    {
        return new MunicipioResumoResource($municipio);
    }
}
