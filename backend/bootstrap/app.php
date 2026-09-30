<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn (Request $request): bool => $request->is('api/*'));

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $status = match (true) {
                $exception instanceof ValidationException => 422,
                $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
                default => 500,
            };

            $detail = match (true) {
                $status >= 500 => 'Ocorreu um erro interno ao processar a solicitação.',
                // Mensagem padrão do roteador vem em inglês e expõe o caminho: troca por uma neutra.
                $exception instanceof NotFoundHttpException && str_starts_with($exception->getMessage(), 'The route') => 'Recurso não encontrado.',
                default => $exception->getMessage() ?: 'A solicitação não pôde ser processada.',
            };

            $payload = [
                'type' => 'about:blank',
                'title' => match ($status) {
                    404 => 'Não encontrado',
                    422 => 'Parâmetros inválidos',
                    default => $status >= 500 ? 'Erro interno' : 'Erro',
                },
                'status' => $status,
                'detail' => $detail,
            ];

            if ($exception instanceof ValidationException) {
                $payload['errors'] = $exception->errors();
            }

            return response()->json($payload, $status, [
                'Content-Type' => 'application/problem+json',
            ]);
        });
    })->create();
