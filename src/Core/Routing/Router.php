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
    /** @var array<string, array<int, array{path: string, handler: Closure(Request): Response, pattern: string, parameters: list<string>}>> */
    private array $routes = [];

    public function get(string $path, Closure $handler): void
    {
        $this->map('GET', $path, $handler);
    }

    public function map(string $method, string $path, Closure $handler): void
    {
        $normalizedPath = $this->normalizePath($path);
        $this->routes[strtoupper($method)][] = [
            'path' => $normalizedPath,
            'handler' => $handler,
            'pattern' => $this->compilePathPattern($normalizedPath),
            'parameters' => $this->extractParameterNames($normalizedPath),
        ];
    }

    public function dispatch(Request $request): Response
    {
        $method = strtoupper($request->method());
        $path = $this->normalizePath($request->path());
        $matchedRoute = $this->matchRoute($method, $path);

        if ($matchedRoute !== null) {
            return $matchedRoute['handler']($request->withRouteParameters($matchedRoute['parameters']));
        }

        if ($this->hasPath($path)) {
            throw new MethodNotAllowedHttpException($this->allowedMethods($path));
        }

        throw new NotFoundHttpException();
    }

    private function hasPath(string $path): bool
    {
        foreach (array_keys($this->routes) as $method) {
            if ($this->matchRoute($method, $path) !== null) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string> */
    private function allowedMethods(string $path): array
    {
        $allowed = [];

        foreach (array_keys($this->routes) as $method) {
            if ($this->matchRoute($method, $path) !== null) {
                $allowed[] = $method;
            }
        }

        sort($allowed);

        return $allowed;
    }

    /** @return array{handler: Closure(Request): Response, parameters: array<string, string>}|null */
    private function matchRoute(string $method, string $path): ?array
    {
        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $path, $matches) !== 1) {
                continue;
            }

            $parameters = [];
            foreach ($route['parameters'] as $parameter) {
                if (isset($matches[$parameter])) {
                    $parameters[$parameter] = $matches[$parameter];
                }
            }

            return [
                'handler' => $route['handler'],
                'parameters' => $parameters,
            ];
        }

        return null;
    }

    private function normalizePath(string $path): string
    {
        if ($path === '' || $path === '/') {
            return '/';
        }

        return '/'.trim($path, '/');
    }

    private function compilePathPattern(string $path): string
    {
        $segments = explode('/', trim($path, '/'));
        $compiled = array_map(static function (string $segment): string {
            if (preg_match('/^\{([A-Za-z_][A-Za-z0-9_]*)\}$/', $segment, $matches) === 1) {
                return '(?P<'.$matches[1].'>[^/]+)';
            }

            return preg_quote($segment, '#');
        }, $segments);

        if ($compiled === []) {
            return '#^/$#';
        }

        return '#^/'.implode('/', $compiled).'$#';
    }

    /** @return list<string> */
    private function extractParameterNames(string $path): array
    {
        preg_match_all('/\{([A-Za-z_][A-Za-z0-9_]*)\}/', $path, $matches);

        return $matches[1] ?? [];
    }
}
