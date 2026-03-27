<?php

declare(strict_types=1);

namespace Tests\Feature;

final class NotFoundEndpointTest extends KernelTestCase
{
    public function test_unknown_route_returns_expected_404_json(): void
    {
        $response = $this->dispatch('GET', '/missing-route');

        self::assertSame(404, $response->status());
        self::assertSame('application/json; charset=utf-8', $response->headers()['Content-Type']);
        self::assertSame(
            [
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Route not found',
                    'context' => [
                        'method' => 'GET',
                        'path' => '/missing-route',
                    ],
                    'report' => [
                        'should_report' => false,
                    ],
                    'render' => [
                        'status' => 404,
                        'headers' => [],
                    ],
                ],
            ],
            $response->payload()
        );
    }
}
