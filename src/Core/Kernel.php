<?php

declare(strict_types=1);

namespace Folio\Core;

use Folio\Core\Config\ConfigRepository;
use Folio\Core\Contracts\Debug\ExceptionHandler;
use Folio\Core\Foundation\Application;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use Throwable;

final class Kernel
{
    private readonly Application $app;

    public function __construct(string $basePath)
    {
        $this->app = Application::configure($basePath);
    }

    public function handle(Request $request): Response
    {
        return $this->app->handle($request);
    }

    public function report(Throwable $exception, bool $debug = false): Response
    {
        if ($debug) {
            $this->app->instance('config', new ConfigRepository([
                'app' => ['debug' => true],
            ]));
        }

        /** @var ExceptionHandler $handler */
        $handler = $this->app->make(ExceptionHandler::class);
        $handler->report($exception);

        return $handler->render(new Request('GET', '/'), $exception);
    }

    public function config(string $key, mixed $default = null): mixed
    {
        $this->app->bootstrap();

        return $this->app->config($key, $default);
    }
}
