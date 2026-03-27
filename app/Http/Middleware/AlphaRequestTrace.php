<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Folio\Core\Contracts\Http\Middleware;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;

final class AlphaRequestTrace implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $payload = $response->payload();
        $payload['meta'] = array_replace($payload['meta'] ?? [], [
            'alpha' => [
                'request_trace' => sprintf('%s %s', $request->method(), $request->path()),
            ],
        ]);

        return Response::json($payload, $response->status(), $response->headers());
    }
}
