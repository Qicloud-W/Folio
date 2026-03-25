<?php

declare(strict_types=1);

namespace Folio\Core\Routing;

use Closure;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;

final class Router
{
    /** @var array<string, array<string, Closure(Request): Response>> */
    private array $routes = [];

    public function get(string $path, Closure $handler): void
    {
        $this->map('GET', $path, $handler);
    }

    public function map(string $method, string $path, Closure $handler): void
    {
        $this->routes[strtoupper($method)][$path] = $handler;
    }

    public function dispatch(Request $request): Response
    {
        $handler = $this->routes[$request->method()][$request->path()] ?? null;

        if ($handler === null) {
            return Response::json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Route not found',
                ],
            ], 404);
        }

        return $handler($request);
    }
}
