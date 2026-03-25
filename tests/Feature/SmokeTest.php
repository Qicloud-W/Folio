<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

final class SmokeTest extends TestCase
{
    private string $entrypoint;

    protected function setUp(): void
    {
        parent::setUp();
        $this->entrypoint = dirname(__DIR__, 2).'/public/index.php';
    }

    public function testHealthRouteReturnsOkPayload(): void
    {
        $result = $this->runRequest('GET', '/health');

        self::assertSame(200, $result['status']);
        self::assertSame('ok', $result['body']['status']);
        self::assertSame('Folio', $result['body']['app']);
        self::assertSame('local', $result['body']['env']);
    }

    public function testPingRouteReturnsPongPayload(): void
    {
        $result = $this->runRequest('GET', '/api/v1/ping');

        self::assertSame(200, $result['status']);
        self::assertSame('pong', $result['body']['message']);
        self::assertSame('zh-CN', $result['body']['locale']);
    }

    public function testUnknownRouteReturnsJson404(): void
    {
        $result = $this->runRequest('GET', '/missing');

        self::assertSame(404, $result['status']);
        self::assertSame('NOT_FOUND', $result['body']['error']['code']);
        self::assertSame('Route not found', $result['body']['error']['message']);
    }

    public function testKnownPathWithWrongMethodReturns405(): void
    {
        $result = $this->runRequest('POST', '/api/v1/ping');

        self::assertSame(405, $result['status']);
        self::assertSame('METHOD_NOT_ALLOWED', $result['body']['error']['code']);
        self::assertSame(['GET'], $result['body']['error']['allowed_methods']);
    }

    /** @return array{status:int,body:array<string,mixed>} */
    private function runRequest(string $method, string $uri): array
    {
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;

        http_response_code(200);
        ob_start();
        require $this->entrypoint;
        $output = (string) ob_get_clean();

        return [
            'status' => http_response_code(),
            'body' => json_decode($output, true, 512, JSON_THROW_ON_ERROR),
        ];
    }
}
