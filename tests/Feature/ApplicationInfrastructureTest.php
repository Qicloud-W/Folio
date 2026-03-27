<?php

declare(strict_types=1);

namespace Tests\Feature;

use Folio\Core\Container\Container;
use Folio\Core\Foundation\Application;
use Folio\Core\I18n\Lang;
use Folio\Core\Pipeline\Pipeline;
use Folio\Core\Providers\TranslationServiceProvider;

final class ApplicationInfrastructureTest extends KernelTestCase
{
    public function test_application_bootstraps_and_resolves_singletons(): void
    {
        $app = Application::configure(dirname(__DIR__, 2))->bootstrap();

        self::assertTrue($app->bound('config'));
        self::assertTrue($app->registered(TranslationServiceProvider::class));
        self::assertInstanceOf(Container::class, $app->container());
        self::assertSame('Folio', $app->config('app.name'));
        self::assertInstanceOf(Lang::class, $app->make('translator'));
        self::assertSame($app->make(Lang::class), $app->make('translator'));
        self::assertSame($app->make(Lang::class), $app->make(Lang::class));
    }

    public function test_pipeline_runs_middleware_in_order(): void
    {
        $app = Application::configure(dirname(__DIR__, 2))->bootstrap();
        $trace = [];

        $result = (new Pipeline($app))
            ->send('request')
            ->through([
                function (string $passable, callable $next) use (&$trace): string {
                    $trace[] = 'first:before';
                    $result = $next($passable.'-a');
                    $trace[] = 'first:after';

                    return $result.'-c';
                },
                function (string $passable, callable $next) use (&$trace): string {
                    $trace[] = 'second:before';
                    $result = $next($passable.'-b');
                    $trace[] = 'second:after';

                    return $result;
                },
            ])
            ->then(function (string $passable) use (&$trace): string {
                $trace[] = 'destination';

                return $passable;
            });

        self::assertSame('request-a-b-c', $result);
        self::assertSame(['first:before', 'second:before', 'destination', 'second:after', 'first:after'], $trace);
    }
}
