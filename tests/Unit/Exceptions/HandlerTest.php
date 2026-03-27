<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use Folio\Core\Config\ConfigRepository;
use Folio\Core\Exceptions\Handler;
use Folio\Core\Exceptions\MethodNotAllowedHttpException;
use Folio\Core\Exceptions\NotFoundHttpException;
use Folio\Core\Http\Request;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class HandlerTest extends TestCase
{
    public function test_http_exception_uses_unified_error_shape(): void
    {
        $handler = new Handler(new ConfigRepository(['app' => ['debug' => false]]));
        $request = new Request('POST', '/api/v1/ping');

        $response = $handler->render($request, new MethodNotAllowedHttpException(['GET']));

        self::assertSame(405, $response->status());
        self::assertSame('GET', $response->headers()['Allow']);
        self::assertSame(
            [
                'error' => [
                    'code' => 'METHOD_NOT_ALLOWED',
                    'message' => 'Method not allowed',
                    'meta' => ['allowed_methods' => ['GET']],
                    'context' => ['method' => 'POST', 'path' => '/api/v1/ping'],
                    'report' => ['should_report' => false],
                    'render' => [
                        'status' => 405,
                        'headers' => ['Allow' => 'GET'],
                    ],
                ],
            ],
            $response->payload()
        );
    }

    public function test_debug_mode_exposes_debug_boundary_for_internal_errors(): void
    {
        $handler = new Handler(new ConfigRepository(['app' => ['debug' => true]]));
        $request = new Request('GET', '/boom');

        $response = $handler->render($request, new RuntimeException('boom'));

        self::assertSame(500, $response->status());
        self::assertSame('boom', $response->payload()['error']['message']);
        self::assertSame(['method' => 'GET', 'path' => '/boom'], $response->payload()['error']['context']);
        self::assertSame(['exception' => RuntimeException::class], $response->payload()['error']['debug']);
        self::assertSame(['should_report' => true], $response->payload()['error']['report']);
        self::assertSame(['status' => 500, 'headers' => []], $response->payload()['error']['render']);
    }

    public function test_not_found_keeps_meta_boundary_absent_when_empty(): void
    {
        $handler = new Handler(new ConfigRepository(['app' => ['debug' => false]]));

        $response = $handler->render(new Request('GET', '/missing'), new NotFoundHttpException('missing'));

        self::assertArrayNotHasKey('meta', $response->payload()['error']);
        self::assertSame(['should_report' => false], $response->payload()['error']['report']);
    }
}
