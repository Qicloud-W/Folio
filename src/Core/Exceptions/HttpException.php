<?php

declare(strict_types=1);

namespace Folio\Core\Exceptions;

use RuntimeException;

class HttpException extends RuntimeException
{
    public static function notFound(string $message = 'Route not found'): NotFoundHttpException
    {
        return new NotFoundHttpException($message);
    }

    public function __construct(
        private readonly int $status,
        private readonly string $errorCode,
        string $message,
        private readonly array $headers = [],
    ) {
        parent::__construct($message, $status);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function meta(): array
    {
        return [];
    }
}
