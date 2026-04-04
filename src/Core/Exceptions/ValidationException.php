<?php

declare(strict_types=1);

namespace Folio\Core\Exceptions;

final class ValidationException extends HttpException
{
    /** @param array<string, list<string>> $errors */
    public function __construct(private readonly array $errors)
    {
        parent::__construct(422, 'VALIDATION_FAILED', 'The given data was invalid.');
    }

    /** @return array<string, list<string>> */
    public function errors(): array
    {
        return $this->errors;
    }

    public function meta(): array
    {
        return ['errors' => $this->errors];
    }
}
