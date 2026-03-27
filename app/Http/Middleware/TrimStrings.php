<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;

final class TrimStrings
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
