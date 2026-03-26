<?php

declare(strict_types=1);

namespace Folio\Core\Http;

use Closure;
use Folio\Core\Container\Container;

final class MiddlewarePipeline
{
    /** @param list<class-string|object|callable> $middlewares */
    public function __construct(
        private readonly Container $container,
        private readonly array $middlewares = [],
    ) {
    }

    public function process(Request $request, Closure $destination): Response
    {
        $runner = array_reduce(
            array_reverse($this->middlewares),
            fn (Closure $next, mixed $middleware): Closure => fn (Request $request): Response => $this->handle($middleware, $request, $next),
            $destination,
        );

        return $runner($request);
    }

    private function handle(mixed $middleware, Request $request, Closure $next): Response
    {
        if (is_string($middleware)) {
            $middleware = $this->container->make($middleware);
        }

        if (is_callable($middleware)) {
            return $middleware($request, $next);
        }

        return $middleware->handle($request, $next);
    }
}
