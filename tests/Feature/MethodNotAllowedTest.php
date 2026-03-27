<?php

declare(strict_types=1);

namespace Tests\Feature;

final class MethodNotAllowedTest extends KernelTestCase
{
    public function test_known_route_with_wrong_method_returns_expected_405_json(): void
    {
        $response = $this->dispatch('POST', '/api/v1/ping');

        self::assertSame(405, $response->status());
        self::assertSame('application/json; charset=utf-8', $response->headers()['Content-Type']);
        self::assertSame('GET', $response->headers()['Allow']);
        self::assertSame(
            [
                'error' => [
                    'code' => 'METHOD_NOT_ALLOWED',
                    'message' => 'Method not allowed',
                    'meta' => [
                        'allowed_methods' => ['GET'],
                    ],
                    'context' => [
                        'method' => 'POST',
                        'path' => '/api/v1/ping',
                    ],
                    'report' => [
                        'should_report' => false,
                    ],
                    'render' => [
                        'status' => 405,
                        'headers' => [
                            'Allow' => 'GET',
                        ],
                    ],
                ],
            ],
            $response->payload()
        );
    }
}
