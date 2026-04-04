<?php

declare(strict_types=1);

namespace Folio\Core\Http;

final class Request
{
    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $server
     * @param array<string, string> $routeParameters
     */
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query = [],
        private readonly array $server = [],
        private readonly mixed $body = null,
        private readonly array $routeParameters = [],
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $rawBody = file_get_contents('php://input');
        $decoded = json_decode($rawBody ?: 'null', true);

        return new self(
            method: $method,
            path: $path,
            query: $_GET,
            server: $_SERVER,
            body: json_last_error() === JSON_ERROR_NONE ? $decoded : $rawBody,
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    /** @return array<string, mixed> */
    public function query(): array
    {
        return $this->query;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /** @return array<string, mixed> */
    public function server(): array
    {
        return $this->server;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        $normalized = 'HTTP_'.strtoupper(str_replace('-', '_', $key));

        return $this->server[$normalized] ?? $this->server[strtoupper($key)] ?? $default;
    }

    public function body(): mixed
    {
        return $this->body;
    }

    public function bodyInput(string $key, mixed $default = null): mixed
    {
        if (!is_array($this->body)) {
            return $default;
        }

        return $this->body[$key] ?? $default;
    }

    /** @return array<string, string> */
    public function routeParameters(): array
    {
        return $this->routeParameters;
    }

    public function routeParameter(string $key, mixed $default = null): mixed
    {
        return $this->routeParameters[$key] ?? $default;
    }

    /** @param array<string, string> $parameters */
    public function withRouteParameters(array $parameters): self
    {
        return new self(
            method: $this->method,
            path: $this->path,
            query: $this->query,
            server: $this->server,
            body: $this->body,
            routeParameters: $parameters,
        );
    }
}
