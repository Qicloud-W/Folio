<?php

declare(strict_types=1);

use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use Folio\Core\Routing\Router;

return static function (Router $router, string $locale, string $pingMessage): void {
    $router->get('/api/v1/ping', static fn (Request $request): Response => Response::json([
        'message' => $pingMessage,
        'locale' => $locale,
    ]));
};
