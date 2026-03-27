<?php

declare(strict_types=1);

namespace Folio\Core\Exceptions;

use Folio\Core\Config\ConfigRepository;
use Folio\Core\Contracts\Debug\ExceptionHandler;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use Throwable;

final class Handler implements ExceptionHandler
{
    public function __construct(private readonly ConfigRepository $config)
    {
    }

    public function report(Throwable $exception): void
    {
        error_log($exception->__toString());
    }

    public function render(Request $request, Throwable $exception): Response
    {
        $debug = (bool) $this->config->get('app.debug', false);

        if ($exception instanceof MethodNotAllowedHttpException) {
            return Response::safeJson([
                'error' => array_filter([
                    'code' => $exception->errorCode(),
                    'message' => $exception->status() >= 500 && !$debug ? 'Internal Server Error' : $exception->getMessage(),
                    'allowed_methods' => $exception->allowedMethods(),
                ], static fn (mixed $value): bool => $value !== null),
            ], $exception->status(), $exception->headers());
        }

        if ($exception instanceof HttpException) {
            return Response::safeJson([
                'error' => array_filter([
                    'code' => $exception->errorCode(),
                    'message' => $exception->status() >= 500 && !$debug ? 'Internal Server Error' : $exception->getMessage(),
                    ...$exception->meta(),
                ], static fn (mixed $value): bool => $value !== null),
            ], $exception->status(), $exception->headers());
        }

        return Response::safeJson([
            'error' => [
                'code' => 'INTERNAL_SERVER_ERROR',
                'message' => $debug ? $exception->getMessage() : 'Internal Server Error',
            ],
        ], 500);
    }
}
