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

    public function test_router_prefix_group_scopes_routes_without_leaking_to_following_routes(): void
    {
        $router = new Router();

        $router->group('/api/v1', static function (Router $router): void {
            $router->get('/users/{user}', static function (Request $request): Response {
                return Response::json([
                    'user' => $request->routeParameter('user'),
                    'all' => $request->routeParameters(),
                ]);
            });
        });

        $router->get('/health', static fn (): Response => Response::json(['ok' => true]));

        $grouped = $router->dispatch(new Request('GET', '/api/v1/users/42'));
        $root = $router->dispatch(new Request('GET', '/health'));

        self::assertSame(['user' => '42', 'all' => ['user' => '42']], $grouped->payload());
        self::assertSame(['ok' => true], $root->payload());
    }

    public function test_router_nested_group_composes_prefix_boundaries_correctly(): void
    {
        $router = new Router();

        $router->group('/api', static function (Router $router): void {
            $router->group('/v1', static function (Router $router): void {
                $router->get('/teams/{team}/users/{user}', static function (Request $request): Response {
                    return Response::json($request->routeParameters());
                });
            });
        });

        $response = $router->dispatch(new Request('GET', '/api/v1/teams/7/users/42'));

        self::assertSame(['team' => '7', 'user' => '42'], $response->payload());
    }

    public function test_router_group_name_prefix_and_explicit_route_name_create_named_route_placeholder(): void
    {
        $router = new Router();

        $router->group('/api', static function (Router $router): void {
            $router->group('/v1', static function (Router $router): void {
                $router->get('/users/{user}', static fn (): Response => Response::json(['ok' => true]))->name('users.show');
            }, name: 'v1.');
        }, name: 'api.');

        self::assertSame('api.v1.users.show', $router->routeName('GET', '/api/v1/users/42'));
    }

    public function test_router_group_name_prefix_does_not_leak_to_routes_outside_group(): void
    {
        $router = new Router();

        $router->group('/api', static function (Router $router): void {
            $router->get('/users/{user}', static fn (): Response => Response::json(['ok' => true]))->name('users.show');
        }, name: 'api.');

        $router->get('/health', static fn (): Response => Response::json(['ok' => true]))->name('health');

        self::assertSame('api.users.show', $router->routeName('GET', '/api/users/42'));
        self::assertSame('health', $router->routeName('GET', '/health'));
    }
}
