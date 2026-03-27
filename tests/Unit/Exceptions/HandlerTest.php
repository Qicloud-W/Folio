<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use Folio\Core\Config\ConfigRepository;
use Folio\Core\Exceptions\Handler;
use Folio\Core\Exceptions\HttpException;
use Folio\Core\Exceptions\MethodNotAllowedHttpException;
use Folio\Core\Http\Request;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class HandlerTest extends TestCase
{
    public function test_it_renders_http_exception_payload_via_single_contract(): void
    {
        $handler = new Handler(new ConfigRepository(['app' => ['debug' => false]]));
        $request = new Request('GET', '/missing');
        $response = $handler->render($request, HttpException::notFound('missing'));

        self::assertSame(404, $response->status());
        self::assertSame('NOT_FOUND', $response->payload()['error']['code']);
        self::assertSame('missing', $response->payload()['error']['message']);
    }

    public function test_it_keeps_allowed_methods_and_allow_header_for_405(): void
    {
        $handler = new Handler(new ConfigRepository(['app' => ['debug' => false]]));
        $request = new Request('POST', '/ping');
        $exception = new MethodNotAllowedHttpException(['GET'], 'method not allowed');
        $response = $handler->render($request, $exception);

        self::assertSame(405, $response->status());
        self::assertSame('GET', $response->headers()['Allow']);
        self::assertSame(['GET'], $response->payload()['error']['allowed_methods']);
    }

    public function test_it_sanitizes_500_errors_when_debug_is_disabled(): void
    {
        $handler = new Handler(new ConfigRepository(['app' => ['debug' => false]]));
        $request = new Request('GET', '/boom');
        $response = $handler->render($request, new RuntimeException('secret failure'));

        self::assertSame(500, $response->status());
        self::assertSame('Internal Server Error', $response->payload()['error']['message']);
        self::assertArrayNotHasKey('debug', $response->payload()['error']);
    }

    public function test_it_exposes_debug_context_when_debug_is_enabled(): void
    {
        $handler = new Handler(new ConfigRepository(['app' => ['debug' => true]]));
        $request = new Request('DELETE', '/boom');
        $response = $handler->render($request, new RuntimeException('secret failure'));

        self::assertSame(500, $response->status());
        self::assertSame('secret failure', $response->payload()['error']['message']);
        self::assertSame(RuntimeException::class, $response->payload()['error']['debug']['exception']);
        self::assertSame('/boom', $response->payload()['error']['debug']['path']);
        self::assertSame('DELETE', $response->payload()['error']['debug']['method']);
    }
}
