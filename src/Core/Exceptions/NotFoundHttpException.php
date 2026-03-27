<?php

declare(strict_types=1);

namespace Folio\Core\Exceptions;

final class NotFoundHttpException extends HttpException
{
    public function __construct(string $message = 'Route not found')
    {
        parent::__construct(404, 'NOT_FOUND', $message);
    }
}
