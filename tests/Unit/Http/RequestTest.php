<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use Folio\Core\Http\Request;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    public function test_request_exposes_query_body_header_and_route_inputs(): void
    {
        $request = new Request(
            'POST',
            '/api/v1/users/42',
            ['page' => 2, 'filter' => 'active'],
            ['HTTP_X_TRACE_ID' => 'trace-123', 'CONTENT_TYPE' => 'application/json'],
            ['name' => '楚锦', 'age' => 18, 'tags' => ['core']],
            ['user' => '42'],
        );

        self::assertSame(2, $request->input('page'));
        self::assertSame('active', $request->input('filter'));
        self::assertSame('fallback', $request->input('missing', 'fallback'));
        self::assertSame('trace-123', $request->header('X-Trace-Id'));
        self::assertSame('application/json', $request->header('CONTENT_TYPE'));
        self::assertSame('楚锦', $request->bodyInput('name'));
        self::assertSame(['core'], $request->bodyInput('tags'));
        self::assertSame('42', $request->routeParameter('user'));
    }

    public function test_request_body_input_returns_default_when_body_is_not_array(): void
    {
        $request = new Request('POST', '/submit', [], [], 'raw-body');

        self::assertSame('fallback', $request->bodyInput('name', 'fallback'));
    }
}
