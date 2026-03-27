<?php

declare(strict_types=1);

namespace Folio\Core;

use Folio\Core\Application\Application;
use Folio\Core\Config\ConfigRepository;
use Folio\Core\Exceptions\HttpException;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use Throwable;

final class Kernel
{
    private readonly Application $app;

    public function __construct(string $basePath)
    {
        $this->app = (new Application($basePath))->bootstrap();
    }

    public function handle(Request $request): Response
    {
        return $this->app->handle($request);
    }

    public function report(Throwable $exception, bool $debug = false): Response
    {
        if ($debug) {
            $config = new ConfigRepository([
                'app' => ['debug' => true],
            ]);
            $this->app->container()->instance(ConfigRepository::class, $config);
            $this->app->container()->instance('config', $config);
        }

        return $this->renderThrowable($exception);
    }

    public function config(string $key, mixed $default = null): mixed
    {
        return $this->app->container()->make(ConfigRepository::class)->get($key, $default);
    }

    private function renderThrowable(Throwable $exception): Response
    {
        $debug = (bool) $this->app->container()->make(ConfigRepository::class)->get('app.debug', false);

        if ($exception instanceof HttpException) {
            return Response::safeJson([
                'error' => array_filter([
                    'code' => $exception->errorCode(),
                    'message' => $exception->getMessage(),
                    ...$exception->meta(),
                ], static fn (mixed $value): bool => $value !== null),
            ], $exception->status(), $exception->status() === 405 && isset($exception->meta()['allowed_methods'])
                ? ['Allow' => implode(', ', $exception->meta()['allowed_methods'])]
                : []);
        }

        return Response::safeJson([
            'error' => [
                'code' => 'INTERNAL_SERVER_ERROR',
                'message' => $debug ? $exception->getMessage() : 'Internal Server Error',
            ],
        ], 500);
    }
}
