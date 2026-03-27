<?php

declare(strict_types=1);

namespace Folio\Core;

use Folio\Core\Config\ConfigRepository;
use Folio\Core\Foundation\Application;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use Throwable;

final class Kernel
{
    private readonly Application $app;

    public function __construct(string $basePath)
    {
        $this->app = Application::configure($basePath)->bootstrap();
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
            $this->app->instance(ConfigRepository::class, $config);
            $this->app->instance('config', $config);
        }

        return $this->app->report($exception);
    }

    public function config(string $key, mixed $default = null): mixed
    {
        return $this->app->config($key, $default);
    }
}
