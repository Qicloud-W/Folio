<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use Closure;
use Folio\Core\Container\Container;
use Folio\Core\Http\MiddlewarePipeline;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use PHPUnit\Framework\TestCase;

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
}
