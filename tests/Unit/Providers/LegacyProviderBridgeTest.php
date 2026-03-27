<?php

declare(strict_types=1);

namespace Tests\Unit\Providers;

use Folio\Core\Container\Container;
use Folio\Core\Contracts\Debug\ExceptionHandler;
use Folio\Core\Foundation\Application;
use Folio\Core\Providers\RoutingServiceProvider;
use Folio\Core\Providers\TranslationServiceProvider;
use Folio\Core\Routing\Router;
use PHPUnit\Framework\TestCase;

final class LegacyProviderBridgeTest extends TestCase
{
    public function test_legacy_application_registers_translation_provider_into_real_container(): void
    {
        $app = Application::configure(dirname(__DIR__, 3))->bootstrap();

        self::assertTrue($app->registered(TranslationServiceProvider::class));
        self::assertTrue($app->bound('translator'));
        self::assertSame($app->make(\Folio\Core\I18n\Lang::class), $app->make('translator'));
    }

    public function test_legacy_application_can_register_container_backed_routing_provider(): void
    {
        $app = Application::configure(dirname(__DIR__, 3))->bootstrap();

        $provider = $app->register(RoutingServiceProvider::class);

        self::assertInstanceOf(RoutingServiceProvider::class, $provider);
        self::assertTrue($app->registered(RoutingServiceProvider::class));
        self::assertInstanceOf(Router::class, $app->make(Router::class));
    }

    public function test_legacy_provider_bridge_exposes_runtime_singletons_through_legacy_container(): void
    {
        $app = Application::configure(dirname(__DIR__, 3))->bootstrap();

        self::assertInstanceOf(ExceptionHandler::class, $app->make(ExceptionHandler::class));
        self::assertSame($app->make(ExceptionHandler::class), $app->container()->make(ExceptionHandler::class));
    }

    public function test_base_application_exposes_container_for_provider_registration(): void
    {
        $app = Application::configure(dirname(__DIR__, 3))->bootstrap();

        self::assertInstanceOf(Container::class, $app->container());
    }
}
