<?php

declare(strict_types=1);

namespace Folio\Core\Contracts\Foundation;

use Folio\Core\Contracts\Container\Container;
use Folio\Core\Contracts\Http\Middleware;
use Folio\Core\Contracts\ServiceProvider;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;

interface Application extends Container
{
    public static function configure(string $basePath): self;

    public function basePath(string $path = ''): string;

    public function bootstrap(): self;

    public function withMiddleware(array $middlewares): self;

    public function prependMiddleware(string|Middleware|callable $middleware): self;

    public function appendMiddleware(string|Middleware|callable $middleware): self;

    public function middleware(): array;

    public function handle(Request $request): Response;

    public function register(ServiceProvider|string $provider): ServiceProvider;

    public function registered(string $provider): bool;

    public function config(string $key, mixed $default = null): mixed;
}
