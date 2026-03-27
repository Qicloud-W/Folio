<?php

declare(strict_types=1);

namespace Tests\Unit\Providers;

use Folio\Core\Application\Application;
use Folio\Core\I18n\Lang;
use PHPUnit\Framework\TestCase;

final class AppServiceProviderTest extends TestCase
{
    public function test_app_service_provider_registers_lang_singleton(): void
    {
        $application = (new Application(dirname(__DIR__, 3)))->bootstrap();
        $container = $application->container();

        $first = $container->make(Lang::class);
        $second = $container->make(Lang::class);

        self::assertInstanceOf(Lang::class, $first);
        self::assertSame($first, $second);
    }
}
