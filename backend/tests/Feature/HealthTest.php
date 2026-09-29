<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\Concerns\ValidaContratoOpenApi;
use Tests\TestCase;

class HealthTest extends TestCase
{
    use ValidaContratoOpenApi;

    public function test_health_returns_ok(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertOk()->assertExactJson(['status' => 'ok']);
        $this->validarContrato($response, '/api/health');
    }

    public function test_unknown_api_routes_return_problem_details(): void
    {
        foreach (['/api/nao-existe', '/api/v1/nao-existe'] as $uri) {
            $this->get($uri)
                ->assertNotFound()
                ->assertHeader('Content-Type', 'application/problem+json')
                ->assertJsonPath('status', 404)
                ->assertJsonStructure(['type', 'title', 'status', 'detail']);
        }
    }

    public function test_unexpected_api_errors_are_generic_and_not_cached(): void
    {
        Route::get('/api/falha-de-teste', static function (): never {
            throw new RuntimeException('segredo SQL que não pode vazar');
        });

        $this->get('/api/falha-de-teste')
            ->assertStatus(500)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('status', 500)
            ->assertJsonMissing(['detail' => 'segredo SQL que não pode vazar'])
            ->assertJsonMissingPath('exception');
    }

    public function test_business_cache_returns_etag_and_revalidates(): void
    {
        Route::get('/api/v1/cache-fixture', static fn () => response()->json(['data' => 'ok']))
            ->middleware('cache.headers:public;max_age=86400;etag');

        $first = $this->get('/api/v1/cache-fixture');
        $etag = $first->headers->get('ETag');

        $first->assertOk()->assertHeader('ETag');
        self::assertStringContainsString('public', (string) $first->headers->get('Cache-Control'));
        self::assertStringContainsString('max-age=86400', (string) $first->headers->get('Cache-Control'));
        self::assertNotNull($etag);

        $this->withHeader('If-None-Match', $etag)->get('/api/v1/cache-fixture')
            ->assertStatus(304)
            ->assertSee('');

        $notFound = $this->get('/api/v1/nao-existe');
        self::assertStringNotContainsString('public', (string) $notFound->headers->get('Cache-Control'));
    }
}
