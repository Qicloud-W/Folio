<?php

declare(strict_types=1);

namespace Tests\Feature;

use Folio\Core\Http\Request;
use Folio\Core\Kernel;

final class ExceptionHandlingTest extends KernelTestCase
{
    public function test_method_not_allowed_exception_is_rendered_consistently(): void
    {
        $response = $this->dispatch('POST', '/api/v1/ping');

        self::assertSame(405, $response->status());
        self::assertSame(['GET'], $response->payload()['error']['allowed_methods']);
    }

    public function test_internal_server_error_uses_unified_shape(): void
    {
        $kernel = new Kernel(dirname(__DIR__, 2));
        $response = $kernel->handle(new Request('GET', '/api/v1/ping', ['break' => '1']));

        self::assertSame(500, $response->status());
        self::assertSame('INTERNAL_SERVER_ERROR', $response->payload()['error']['code']);
        self::assertSame('Ping route forced failure', $response->payload()['error']['message']);
    }
}
