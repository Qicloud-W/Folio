<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use Folio\Core\Application\Application;
use Folio\Core\Config\ConfigRepository;
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
        self::assertTrue($container->has('config'));
    }
}
