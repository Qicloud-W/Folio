<?php

declare(strict_types=1);

namespace Folio\Core\Routing;

use Closure;
use Folio\Core\Exceptions\MethodNotAllowedHttpException;
use Folio\Core\Exceptions\NotFoundHttpException;
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
            return $handler($request);
        }

        if ($this->hasPath($path)) {
            throw new MethodNotAllowedHttpException($this->allowedMethods($path));
        }

        throw new NotFoundHttpException();
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
