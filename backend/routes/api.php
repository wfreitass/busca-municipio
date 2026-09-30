<?php

use App\Http\Controllers\Api\V1\MunicipioController;
use App\Http\Controllers\Api\V1\UfController;
use Illuminate\Support\Facades\Route;

Route::get('/health', static fn () => response()->json(['status' => 'ok']))
    ->name('health');

Route::prefix('v1')
    ->middleware('cache.headers:public;max_age=86400;etag')
    ->group(function (): void {
        Route::get('/municipios', [MunicipioController::class, 'buscar']);
        Route::get('/municipios/{municipio}', [MunicipioController::class, 'mostrar'])
            ->where('municipio', '[0-9]{7}')
            ->missing(fn () => abort(404, 'Município não encontrado.'));

        Route::get('/ufs', [UfController::class, 'listar']);
        Route::get('/ufs/{uf}', [UfController::class, 'mostrar'])
            ->where('uf', '[A-Za-z]{2}')
            ->missing(fn () => abort(404, 'UF não encontrada.'));
        Route::get('/ufs/{uf}/municipios', [UfController::class, 'ranking'])
            ->where('uf', '[A-Za-z]{2}')
            ->missing(fn () => abort(404, 'UF não encontrada.'));
    });
