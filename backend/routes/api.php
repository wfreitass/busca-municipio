<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/health', static fn () => response()->json(['status' => 'ok']))
    ->name('health');

Route::prefix('v1')
    ->middleware('cache.headers:public;max_age=86400;etag')
    ->group(function (): void {
        // Rotas de negócio serão adicionadas nas changes seguintes.
    });
