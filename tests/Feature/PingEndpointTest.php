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
        self::assertSame(
            [
                'message' => 'pong',
                'locale' => 'zh-CN',
            ],
            $response->payload()
        );
    }
}
