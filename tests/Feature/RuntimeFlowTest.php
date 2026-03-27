<?php

declare(strict_types=1);

namespace Tests\Feature;

use Folio\Core\Application\Application;
use Folio\Core\Http\Request;
use RuntimeException;

final class RuntimeFlowTest extends KernelTestCase
{
    public function test_runtime_application_handles_health_route_through_main_chain(): void
    {
        $application = (new Application(dirname(__DIR__, 2)))->bootstrap();

        $response = $application->handle(new Request('GET', '/health'));

        self::assertSame(200, $response->status());
        self::assertSame('ok', $response->payload()['status']);
        self::assertSame('Folio', $response->payload()['app']);
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
}
