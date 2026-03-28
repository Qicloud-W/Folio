<?php

declare(strict_types=1);

use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use Folio\Core\Routing\Router;

return static function (Router $router, string $locale, string $pingMessage): void {
    $router->get('/api/v1/ping', static function (Request $request) use ($locale, $pingMessage): Response {
        if (($request->query()['break'] ?? null) === '1') {
            throw new RuntimeException('Ping route forced failure');
        }

        return Response::json([
            'message' => $pingMessage,
            'locale' => $locale,
        ]);
    });

    $router->get('/api/v1/users/{user}', static function (Request $request) use ($locale): Response {
        return Response::json([
            'data' => [
                'user' => $request->routeParameter('user'),
                'route_parameters' => $request->routeParameters(),
            ],
            'locale' => $locale,
        ]);
    });
};
