<?php

declare(strict_types=1);

namespace Folio\Core\Contracts\Debug;

use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use Throwable;

interface ExceptionHandler
{
    public function report(Throwable $exception): void;

    public function render(Request $request, Throwable $exception): Response;
}
