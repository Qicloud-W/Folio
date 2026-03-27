<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use Folio\Core\Application\Application;
use Folio\Core\Config\ConfigRepository;
use Folio\Core\Contracts\Debug\ExceptionHandler;
use Folio\Core\Routing\Router;
use PHPUnit\Framework\TestCase;

final class ApplicationBootstrapTest extends TestCase
{
    public function test_application_bootstrap_registers_core_services(): void
    {
        $application = (new Application(dirname(__DIR__, 3)))->bootstrap();
        $container = $application->container();

        self::assertInstanceOf(ConfigRepository::class, $container->make(ConfigRepository::class));
        self::assertInstanceOf(Router::class, $container->make(Router::class));
        self::assertInstanceOf(ExceptionHandler::class, $container->make(ExceptionHandler::class));
        self::assertTrue($container->has('config'));
    }

    public function test_application_bootstrap_is_idempotent(): void
    {
        $application = new Application(dirname(__DIR__, 3));
        $container = $application->container();

        $application->bootstrap();
        $router = $container->make(Router::class);
        $config = $container->make(ConfigRepository::class);
        $handler = $container->make(ExceptionHandler::class);

        self::assertSame($application, $application->bootstrap());
        self::assertSame($router, $container->make(Router::class));
        self::assertSame($config, $container->make(ConfigRepository::class));
        self::assertSame($handler, $container->make(ExceptionHandler::class));
    }
}
