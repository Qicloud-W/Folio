<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Folio\Core\Contracts\Http\Middleware;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;

final class TrimStrings implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
