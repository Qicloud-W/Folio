<?php

declare(strict_types=1);

namespace Tests\Feature;

final class HealthEndpointTest extends KernelTestCase
{
    public function test_health_endpoint_returns_expected_json(): void
    {
        $response = $this->dispatch('GET', '/health');

        self::assertSame(200, $response->status());
        self::assertSame('application/json; charset=utf-8', $response->headers()['Content-Type']);
        self::assertSame(
            [
                'status' => 'ok',
                'app' => 'Folio',
                'env' => 'local',
            ],
            $response->payload()
        );
    }
}
