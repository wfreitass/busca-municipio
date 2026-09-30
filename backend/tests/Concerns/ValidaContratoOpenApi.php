<?php

namespace Tests\Concerns;

use Illuminate\Testing\TestResponse;
use Osteel\OpenApi\Testing\ValidatorBuilder;
use Symfony\Component\HttpFoundation\Response;

trait ValidaContratoOpenApi
{
    /** @param TestResponse<Response> $response */
    protected function validarContrato(TestResponse $response, string $path, string $method = 'get'): void
    {
        $validator = ValidatorBuilder::fromYamlFile(
            base_path('../docs/api/openapi.yaml')
        )->getValidator();

        $validator->validate($response->baseResponse, $path, $method);
        $this->addToAssertionCount(1);
    }
}
