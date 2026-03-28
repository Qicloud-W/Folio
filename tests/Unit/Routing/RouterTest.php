<?php

declare(strict_types=1);

namespace Tests\Unit\Routing;

use Folio\Core\Exceptions\MethodNotAllowedHttpException;
use Folio\Core\Exceptions\NotFoundHttpException;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use Folio\Core\Routing\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function test_router_dispatches_dynamic_route_and_hydrates_request_route_parameters(): void
    {
        $router = new Router();
        $router->get('/api/v1/users/{user}', static function (Request $request): Response {
            return Response::json([
                'user' => $request->routeParameter('user'),
                'all' => $request->routeParameters(),
            ]);
        });

        $response = $router->dispatch(new Request('GET', '/api/v1/users/42'));

        self::assertSame(200, $response->status());
        self::assertSame('42', $response->payload()['user']);
        self::assertSame(['user' => '42'], $response->payload()['all']);
    }

    public function test_router_reports_method_not_allowed_for_dynamic_route_pattern(): void
    {
        $router = new Router();
        $router->get('/api/v1/users/{user}', static fn (): Response => Response::json(['ok' => true]));

        $this->expectException(MethodNotAllowedHttpException::class);
        $router->dispatch(new Request('POST', '/api/v1/users/42'));
    }

    public function test_router_throws_not_found_when_dynamic_route_pattern_does_not_match(): void
    {
        $router = new Router();
        $router->get('/api/v1/users/{user}', static fn (): Response => Response::json(['ok' => true]));

        $this->expectException(NotFoundHttpException::class);
        $router->dispatch(new Request('GET', '/api/v1/users/42/posts'));
    }
}
