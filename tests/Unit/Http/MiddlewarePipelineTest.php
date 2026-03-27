<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use Closure;
use Folio\Core\Container\Container;
use Folio\Core\Contracts\Http\Middleware as MiddlewareContract;
use Folio\Core\Http\MiddlewarePipeline;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class MiddlewarePipelineTest extends TestCase
{
    public function test_pipeline_executes_middlewares_in_declared_order(): void
    {
        $trace = [];
        $pipeline = new MiddlewarePipeline(new Container(), [
            static function (Request $request, Closure $next) use (&$trace): Response {
                $trace[] = 'mw1-before';
                $response = $next($request);
                $trace[] = 'mw1-after';

                return $response;
            },
            static function (Request $request, Closure $next) use (&$trace): Response {
                $trace[] = 'mw2-before';
                $response = $next($request);
                $trace[] = 'mw2-after';

                return $response;
            },
        ]);

        $response = $pipeline->process(new Request('GET', '/health'), static function (): Response {
            return Response::json(['ok' => true]);
        });

        self::assertSame(200, $response->status());
        self::assertSame(['mw1-before', 'mw2-before', 'mw2-after', 'mw1-after'], $trace);
    }

    public function test_pipeline_short_circuits_when_middleware_returns_response_without_calling_next(): void
    {
        $trace = [];
        $pipeline = new MiddlewarePipeline(new Container(), [
            static function (Request $request, Closure $next) use (&$trace): Response {
                $trace[] = 'mw1-before';
                $response = $next($request);
                $trace[] = 'mw1-after';

                return $response;
            },
            static function (Request $request, Closure $next) use (&$trace): Response {
                $trace[] = 'mw2-short-circuit';

                return Response::json(['short' => true], 202);
            },
        ]);

        $response = $pipeline->process(new Request('GET', '/health'), static function () use (&$trace): Response {
            $trace[] = 'destination';

            return Response::json(['ok' => true]);
        });

        self::assertSame(202, $response->status());
        self::assertSame(['short' => true], $response->payload());
        self::assertSame(['mw1-before', 'mw2-short-circuit', 'mw1-after'], $trace);
    }

    public function test_pipeline_resolves_class_string_middlewares_from_container(): void
    {
        $container = new Container();
        $container->instance(TraceMiddleware::class, new TraceMiddleware());
        $pipeline = new MiddlewarePipeline($container, [TraceMiddleware::class]);

        $response = $pipeline->process(new Request('GET', '/health'), static function (): Response {
            return Response::json(['ok' => true]);
        });

        self::assertSame(['ok' => true, 'trace' => 'class-string'], $response->payload());
    }

    public function test_pipeline_bubbles_middleware_exceptions_to_caller(): void
    {
        $pipeline = new MiddlewarePipeline(new Container(), [
            static function (): never {
                throw new RuntimeException('middleware exploded');
            },
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('middleware exploded');

        $pipeline->process(new Request('GET', '/boom'), static function (): Response {
            return Response::json(['ok' => true]);
        });
    }
}

final class TraceMiddleware implements MiddlewareContract
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        return Response::json(array_replace($response->payload(), [
            'trace' => 'class-string',
        ]), $response->status(), $response->headers());
    }
}
