<?php

declare(strict_types=1);

namespace Tests\Feature;

use Folio\Core\Exceptions\NotFoundHttpException;
use Folio\Core\Foundation\Application;
use RuntimeException;

final class ExceptionHandlingTest extends KernelTestCase
{
    public function test_report_returns_sanitized_500_response_by_default(): void
    {
        $response = (new \Folio\Core\Kernel(dirname(__DIR__, 2)))->report(new RuntimeException('boom'));

        self::assertSame(500, $response->status());
        self::assertSame('INTERNAL_SERVER_ERROR', $response->payload()['error']['code']);
        self::assertSame('Internal Server Error', $response->payload()['error']['message']);
    }

    public function test_report_returns_debug_message_when_debug_enabled(): void
    {
        $response = (new \Folio\Core\Kernel(dirname(__DIR__, 2)))->report(new RuntimeException('boom'), true);

        self::assertSame(500, $response->status());
        self::assertSame('boom', $response->payload()['error']['message']);
    }

    public function test_http_exceptions_are_rendered_via_single_handler_entry(): void
    {
        $response = Application::configure(dirname(__DIR__, 2))
            ->bootstrap()
            ->report(new NotFoundHttpException('missing'));

        self::assertSame(404, $response->status());
        self::assertSame('missing', $response->payload()['error']['message']);
    }
}
