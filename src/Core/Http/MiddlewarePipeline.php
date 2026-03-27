<?php

declare(strict_types=1);

namespace Folio\Core\Http;

use Closure;
use Folio\Core\Container\Container;
use Folio\Core\Contracts\Http\Middleware as MiddlewareContract;
use InvalidArgumentException;

final class MiddlewarePipeline
{
    /** @param list<class-string<MiddlewareContract>|MiddlewareContract|callable> $middlewares */
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
        $resolved = $this->resolve($middleware);

        if (is_callable($resolved)) {
            $response = $resolved($request, $next);
        } elseif ($resolved instanceof MiddlewareContract) {
            $response = $resolved->handle($request, $next);
        } else {
            throw new InvalidArgumentException(sprintf(
                'Middleware [%s] must be a callable or implement %s.',
                is_object($resolved) ? $resolved::class : gettype($resolved),
                MiddlewareContract::class,
            ));
        }

        if (!$response instanceof Response) {
            throw new InvalidArgumentException(sprintf(
                'Middleware [%s] must return %s.',
                is_object($resolved) ? $resolved::class : gettype($resolved),
                Response::class,
            ));
        }

        return $response;
    }

    private function resolve(mixed $middleware): mixed
    {
        if (is_string($middleware)) {
            return $this->container->make($middleware);
        }

        return $middleware;
    }
}
