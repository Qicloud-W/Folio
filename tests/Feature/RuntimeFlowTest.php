<?php

declare(strict_types=1);

namespace Tests\Feature;

use Folio\Core\Application\Application;
use Folio\Core\Contracts\Debug\ExceptionHandler;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use RuntimeException;
use Throwable;

final class RuntimeFlowTest extends KernelTestCase
{
    public function test_runtime_application_handles_health_route_through_main_chain(): void
    {
        $application = (new Application(dirname(__DIR__, 2)))->bootstrap();

        $response = $application->handle(new Request('GET', '/health'));

        self::assertSame(200, $response->status());
        self::assertSame('ok', $response->payload()['status']);
        self::assertSame('Folio', $response->payload()['app']);
        self::assertSame('GET /health', $response->payload()['meta']['alpha']['request_trace']);
    }

    public function test_runtime_application_converts_router_exceptions_via_shared_handler(): void
    {
        $application = (new Application(dirname(__DIR__, 2)))->bootstrap();

        $response = $application->handle(new Request('POST', '/api/v1/ping'));

        self::assertSame(405, $response->status());
        self::assertSame('GET', $response->headers()['Allow']);
        self::assertSame(['GET'], $response->payload()['error']['meta']['allowed_methods']);
    }

    public function test_runtime_application_sanitizes_unhandled_500_when_debug_disabled(): void
    {
        $application = (new Application(dirname(__DIR__, 2)))->bootstrap();
        $application->container()->make(\Folio\Core\Routing\Router::class)->get('/boom', static function (): never {
            throw new RuntimeException('secret failure');
        });

        $response = $application->handle(new Request('GET', '/boom'));

        self::assertSame(500, $response->status());
        self::assertSame('Internal Server Error', $response->payload()['error']['message']);
    }

    public function test_runtime_application_routes_middleware_exceptions_to_exception_handler(): void
    {
        $application = (new Application(dirname(__DIR__, 2)))->bootstrap();
        $handler = new RecordingExceptionHandler();
        $application->container()->instance(ExceptionHandler::class, $handler);
        $application->prependMiddleware(static function (): never {
            throw new RuntimeException('middleware boom');
        });

        $response = $application->handle(new Request('GET', '/health'));

        self::assertSame(500, $response->status());
        self::assertSame('HANDLED_BY_TEST', $response->payload()['error']['code']);
        self::assertSame('middleware boom', $handler->reported?->getMessage());
        self::assertSame('middleware boom', $handler->rendered?->getMessage());
    }

    public function test_runtime_application_supports_programmatic_global_middleware_ordering(): void
    {
        $application = (new Application(dirname(__DIR__, 2)))->bootstrap()->withMiddleware([]);
        $trace = [];

        $application
            ->appendMiddleware(static function (Request $request, callable $next) use (&$trace): Response {
                $trace[] = 'append-before';
                $response = $next($request);
                $trace[] = 'append-after';

                return $response;
            })
            ->prependMiddleware(static function (Request $request, callable $next) use (&$trace): Response {
                $trace[] = 'prepend-before';
                $response = $next($request);
                $trace[] = 'prepend-after';

                return $response;
            });

        $response = $application->handle(new Request('GET', '/health'));

        self::assertSame(200, $response->status());
        self::assertSame([
            'prepend-before',
            'append-before',
            'append-after',
            'prepend-after',
        ], $trace);
    }

    public function test_runtime_application_supports_programmatic_short_circuit_middleware(): void
    {
        $application = (new Application(dirname(__DIR__, 2)))->bootstrap()->withMiddleware([]);
        $application->appendMiddleware(static function (): Response {
            return Response::json(['maintenance' => true], 503);
        });

        $response = $application->handle(new Request('GET', '/health'));

        self::assertSame(503, $response->status());
        self::assertSame(true, $response->payload()['maintenance']);
        self::assertSame('GET /health', $response->payload()['meta']['alpha']['request_trace']);
    }
}

final class RecordingExceptionHandler implements ExceptionHandler
{
    public ?Throwable $reported = null;

    public ?Throwable $rendered = null;

    public function report(Throwable $exception): void
    {
        $this->reported = $exception;
    }

    public function render(Request $request, Throwable $exception): Response
    {
        $this->rendered = $exception;

        return Response::json([
            'error' => [
                'code' => 'HANDLED_BY_TEST',
                'message' => $exception->getMessage(),
                'path' => $request->path(),
            ],
        ], 500);
    }
}
