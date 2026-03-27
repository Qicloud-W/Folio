<?php

declare(strict_types=1);

namespace Folio\Core\Providers;

use Folio\Core\Contracts\Support\DeferrableProvider;
use Folio\Core\I18n\Lang;
use Folio\Core\Support\ServiceProvider;

final class TranslationServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->container->singleton(Lang::class, fn (\Folio\Core\Container\Container $container) => new Lang(
            $container->make('basePath').'/resources/lang'
        ));
        $this->container->singleton('translator', fn (\Folio\Core\Container\Container $container) => $container->make(Lang::class));
    }

    public function provides(): array
    {
        return [Lang::class, 'translator'];
    }
}
