<?php

declare(strict_types=1);

namespace Folio\Core\Contracts\Http;

use Closure;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;

interface Middleware
{
    public function handle(Request $request, Closure $next): Response;
}
