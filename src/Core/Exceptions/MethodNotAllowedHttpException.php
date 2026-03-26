<?php

declare(strict_types=1);

namespace Folio\Core\Exceptions;

final class MethodNotAllowedHttpException extends HttpException
{
    /** @param list<string> $allowedMethods */
    public function __construct(private readonly array $allowedMethods, string $message = 'Method not allowed')
    {
        parent::__construct(405, 'METHOD_NOT_ALLOWED', $message, ['Allow' => implode(', ', $allowedMethods)]);
    }

    /** @return list<string> */
    public function allowedMethods(): array
    {
        return $this->allowedMethods;
    }
}
