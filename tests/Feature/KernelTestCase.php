<?php

declare(strict_types=1);

namespace Tests\Feature;

use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use Folio\Core\Kernel;
use PHPUnit\Framework\TestCase;

abstract class KernelTestCase extends TestCase
{
    protected function dispatch(string $method, string $uri): Response
    {
        $request = new Request($method, $uri, [], [], [], []);

        return (new Kernel(dirname(__DIR__, 2)))->handle($request);
    }
}
