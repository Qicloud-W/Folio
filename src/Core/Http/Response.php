<?php

declare(strict_types=1);

namespace Folio\Core\Http;

use JsonException;
use Throwable;

final class Response
{
    public function __construct(
        private readonly array $payload,
        private readonly int $status = 200,
        private readonly array $headers = ['Content-Type' => 'application/json; charset=utf-8'],
    ) {
    }

    public static function json(array $payload, int $status = 200, array $headers = []): self
    {
        return new self($payload, $status, array_replace(['Content-Type' => 'application/json; charset=utf-8'], $headers));
    }

    public static function safeJson(array $payload, int $status = 200, array $headers = []): self
    {
        try {
            json_encode($payload, JSON_THROW_ON_ERROR);

            return self::json($payload, $status, $headers);
        } catch (Throwable) {
            return new self([
                'error' => [
                    'code' => 'RESPONSE_SERIALIZATION_ERROR',
                    'message' => 'Failed to encode JSON response',
                ],
            ], 500, array_replace(['Content-Type' => 'application/json; charset=utf-8'], $headers));
        }
    }

    public function status(): int
    {
        return $this->status;
    }

    public function payload(): array
    {
        return $this->payload;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header(sprintf('%s: %s', $name, $value));
        }

        try {
            echo json_encode($this->payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            http_response_code(500);
            echo '{"error":{"code":"RESPONSE_SERIALIZATION_ERROR","message":"Failed to encode JSON response"}}';
        }
    }
}
