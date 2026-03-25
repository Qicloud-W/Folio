<?php

declare(strict_types=1);

namespace Tests\Feature;

final class SmokeTest extends KernelTestCase
{
    public function test_health_route_returns_ok_payload(): void
    {
        $response = $this->dispatch('GET', '/health');

        self::assertSame(200, $response->status());
        self::assertSame('ok', $response->payload()['status']);
        self::assertSame('Folio', $response->payload()['app']);
    }

    public function test_ping_route_returns_pong_payload(): void
    {
        $response = $this->dispatch('GET', '/api/v1/ping');

        self::assertSame(200, $response->status());
        self::assertSame('pong', $response->payload()['message']);
        self::assertSame('zh-CN', $response->payload()['locale']);
    }

    public function test_unknown_route_returns_json404(): void
    {
        $response = $this->dispatch('GET', '/missing');

        self::assertSame(404, $response->status());
        self::assertSame('NOT_FOUND', $response->payload()['error']['code']);
        self::assertSame('Route not found', $response->payload()['error']['message']);
    }

    public function test_known_route_with_wrong_method_returns_json405(): void
    {
        $response = $this->dispatch('POST', '/api/v1/ping');

        self::assertSame(405, $response->status());
        self::assertSame('METHOD_NOT_ALLOWED', $response->payload()['error']['code']);
        self::assertSame('Method not allowed', $response->payload()['error']['message']);
    }
}
