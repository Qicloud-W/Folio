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
        $this->app->singleton(Lang::class, fn () => new Lang($this->app->basePath('resources/lang')));
        $this->app->singleton('translator', fn () => $this->app->make(Lang::class));
    }

    public function provides(): array
    {
        return [Lang::class, 'translator'];
    }
}
