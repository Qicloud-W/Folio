<?php

declare(strict_types=1);

namespace Folio\Core\Http;

final class Request
{
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query = [],
        private readonly array $server = [],
        private readonly mixed $body = null,
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

    public function query(): array
    {
        return $this->query;
    }

    public function server(): array
    {
        return $this->server;
    }

    public function body(): mixed
    {
        return $this->body;
    }
}
