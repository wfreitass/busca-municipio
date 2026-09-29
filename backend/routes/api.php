<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\MunicipioController;
use App\Http\Controllers\Api\V1\UfController;
use Illuminate\Support\Facades\Route;

Route::get('/health', static fn () => response()->json(['status' => 'ok']))
    ->name('health');

Route::prefix('v1')
    ->middleware('cache.headers:public;max_age=86400;etag')
    ->group(function (): void {
        Route::get('/municipios', [MunicipioController::class, 'buscar']);
        Route::get('/municipios/{codigo}', [MunicipioController::class, 'mostrar'])->where('codigo', '[0-9]{7}');

        Route::get('/ufs', [UfController::class, 'listar']);
        Route::get('/ufs/{sigla}', [UfController::class, 'mostrar'])->where('sigla', '[A-Za-z]{2}');
        Route::get('/ufs/{sigla}/municipios', [UfController::class, 'ranking'])->where('sigla', '[A-Za-z]{2}');
    });
