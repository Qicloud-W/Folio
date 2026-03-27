<?php

declare(strict_types=1);

namespace Folio\Core\Exceptions;

use Folio\Core\Contracts\Debug\ExceptionHandler;
use Folio\Core\Contracts\Foundation\Application;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use Throwable;

final class Handler implements ExceptionHandler
{
    public function __construct(private readonly Application $app)
    {
    }

    public function report(Throwable $exception): void
    {
        error_log($exception->__toString());
    }

    public function render(Request $request, Throwable $exception): Response
    {
        if ($exception instanceof MethodNotAllowedHttpException) {
            return Response::json([
                'error' => [
                    'code' => $exception->errorCode(),
                    'message' => $exception->getMessage(),
                    'allowed_methods' => $exception->allowedMethods(),
                ],
            ], $exception->status(), $exception->headers());
        }

        if ($exception instanceof HttpException) {
            return Response::json([
                'error' => [
                    'code' => $exception->errorCode(),
                    'message' => $exception->getMessage(),
                ],
            ], $exception->status(), $exception->headers());
        }

        $debug = (bool) $this->app->config('app.debug', false);

        return Response::safeJson([
            'error' => [
                'code' => 'INTERNAL_SERVER_ERROR',
                'message' => $debug ? $exception->getMessage() : 'Internal Server Error',
            ],
        ], 500);
    }
}
