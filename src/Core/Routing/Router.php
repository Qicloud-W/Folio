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
        $method = strtoupper($request->method());
        $path = $request->path();
        $handler = $this->routes[$method][$path] ?? null;

        if ($handler instanceof Closure) {
            try {
                return $handler($request);
            } catch (\Throwable $exception) {
                return Response::safeJson([
                    'error' => [
                        'code' => 'INTERNAL_SERVER_ERROR',
                        'message' => 'Internal Server Error',
                    ],
                ], 500);
            }
        }

        if ($this->hasPath($path)) {
            return Response::json([
                'error' => [
                    'code' => 'METHOD_NOT_ALLOWED',
                    'message' => 'Method not allowed',
                    'allowed_methods' => $this->allowedMethods($path),
                ],
            ], 405, ['Allow' => implode(', ', $this->allowedMethods($path))]);
        }

        return Response::json([
            'error' => [
                'code' => 'NOT_FOUND',
                'message' => 'Route not found',
            ],
        ], 404);
    }

    private function hasPath(string $path): bool
    {
        foreach ($this->routes as $routes) {
            if (array_key_exists($path, $routes)) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string> */
    private function allowedMethods(string $path): array
    {
        $allowed = [];

        foreach ($this->routes as $method => $routes) {
            if (array_key_exists($path, $routes)) {
                $allowed[] = $method;
            }
        }

        sort($allowed);

        return $allowed;
    }
}
