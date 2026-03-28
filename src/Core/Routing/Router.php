<?php

declare(strict_types=1);

namespace Folio\Core\Routing;

use Closure;
use Folio\Core\Exceptions\MethodNotAllowedHttpException;
use Folio\Core\Exceptions\NotFoundHttpException;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use InvalidArgumentException;

final class Router
{
    /** @var array<string, array<int, array{path: string, handler: Closure(Request): Response, pattern: string, parameters: list<string>, name: ?string}>> */
    private array $routes = [];

    /** @var list<string> */
    private array $prefixStack = [];

    /** @var list<string> */
    private array $nameStack = [];

    public function get(string $path, Closure $handler): RouteDefinition
    {
        return $this->map('GET', $path, $handler);
    }

    public function map(string $method, string $path, Closure $handler): RouteDefinition
    {
        $normalizedPath = $this->applyPrefix($path);
        $route = [
            'path' => $normalizedPath,
            'handler' => $handler,
            'pattern' => $this->compilePathPattern($normalizedPath),
            'parameters' => $this->extractParameterNames($normalizedPath),
            'name' => $this->currentNamePrefix(),
        ];

        $method = strtoupper($method);
        $index = isset($this->routes[$method]) ? count($this->routes[$method]) : 0;
        $this->routes[$method][] = $route;

        return new RouteDefinition($this, $method, $index);
    }

    public function group(string $prefix, Closure $routes, string $name = ''): void
    {
        $normalizedPrefix = $this->normalizeGroupPrefix($prefix);
        $normalizedName = $this->normalizeNameFragment($name);

        if ($normalizedPrefix !== '') {
            $this->prefixStack[] = $normalizedPrefix;
        }

        if ($normalizedName !== '') {
            $this->nameStack[] = $normalizedName;
        }

        try {
            $routes($this);
        } finally {
            if ($normalizedName !== '') {
                array_pop($this->nameStack);
            }

            if ($normalizedPrefix !== '') {
                array_pop($this->prefixStack);
            }
        }
    }

    public function prefix(string $prefix, Closure $routes): void
    {
        $this->group($prefix, $routes);
    }

    public function routeName(string $method, string $path): ?string
    {
        $matchedRoute = $this->matchRoute(strtoupper($method), $this->normalizePath($path));

        return $matchedRoute['name'] ?? null;
    }

    /**
     * @param array<string, scalar|null> $parameters
     */
    public function urlFor(string $name, array $parameters = []): string
    {
        foreach ($this->routes as $methodRoutes) {
            foreach ($methodRoutes as $route) {
                if (($route['name'] ?? null) !== $name) {
                    continue;
                }

                return $this->buildPath($route['path'], $route['parameters'], $parameters);
            }
        }

        throw new InvalidArgumentException(sprintf('Route [%s] is not defined.', $name));
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

    public function setRouteName(string $method, int $index, string $name): void
    {
        $normalized = $this->normalizeNameFragment($name, allowTrailingDot: false);
        $fullName = $this->currentNamePrefix().$normalized;

        $this->routes[$method][$index]['name'] = $fullName;
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

    /** @return array{handler: Closure(Request): Response, parameters: array<string, string>, name: ?string}|null */
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
                'name' => $route['name'],
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

    private function applyPrefix(string $path): string
    {
        $prefix = implode('/', $this->prefixStack);
        $suffix = trim($path, '/');

        if ($prefix === '' && $suffix === '') {
            return '/';
        }

        if ($prefix === '') {
            return '/'.$suffix;
        }

        if ($suffix === '') {
            return '/'.$prefix;
        }

        return '/'.$prefix.'/'.$suffix;
    }

    private function normalizeGroupPrefix(string $prefix): string
    {
        return trim($prefix, '/');
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

    /**
     * @param list<string> $expectedParameters
     * @param array<string, scalar|null> $parameters
     */
    private function buildPath(string $path, array $expectedParameters, array $parameters): string
    {
        $resolvedPath = $path;

        foreach ($expectedParameters as $parameter) {
            if (!array_key_exists($parameter, $parameters)) {
                throw new InvalidArgumentException(sprintf('Route [%s] is missing required parameter [%s].', $path, $parameter));
            }

            $value = $parameters[$parameter];

            if ($value === null || $value === '') {
                throw new InvalidArgumentException(sprintf('Route [%s] parameter [%s] must be a non-empty scalar.', $path, $parameter));
            }

            $resolvedPath = str_replace('{'.$parameter.'}', rawurlencode((string) $value), $resolvedPath);
            unset($parameters[$parameter]);
        }

        if ($parameters === []) {
            return $resolvedPath;
        }

        ksort($parameters);

        $query = http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);

        if ($query === '') {
            return $resolvedPath;
        }

        return $resolvedPath.'?'.$query;
    }

    private function currentNamePrefix(): string
    {
        return implode('', $this->nameStack);
    }

    private function normalizeNameFragment(string $name, bool $allowTrailingDot = true): string
    {
        $normalized = trim($name, '. ');

        if ($normalized === '') {
            return '';
        }

        return $allowTrailingDot ? $normalized.'.' : $normalized;
    }
}
