<?php

declare(strict_types=1);

namespace Tests\Feature;

final class PingEndpointTest extends KernelTestCase
{
    public function test_ping_endpoint_returns_expected_json(): void
    {
        $response = $this->dispatch('GET', '/api/v1/ping');

        self::assertSame(200, $response->status());
        self::assertSame('application/json; charset=utf-8', $response->headers()['Content-Type']);
        self::assertSame('pong', $response->payload()['message']);
        self::assertSame('zh-CN', $response->payload()['locale']);
        self::assertSame('GET /api/v1/ping', $response->payload()['meta']['alpha']['request_trace']);
    }

    public function test_dynamic_route_returns_bound_route_parameters(): void
    {
        $response = $this->dispatch('GET', '/api/v1/users/42');

        self::assertSame(200, $response->status());
        self::assertSame('42', $response->payload()['data']['user']);
        self::assertSame(['user' => '42'], $response->payload()['data']['route_parameters']);
        self::assertSame('GET /api/v1/users/42', $response->payload()['meta']['alpha']['request_trace']);
    }
}
