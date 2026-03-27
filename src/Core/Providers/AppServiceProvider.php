<?php

declare(strict_types=1);

namespace Folio\Core\Providers;

use Folio\Core\Config\ConfigRepository;
use Folio\Core\Container\Container;
use Folio\Core\Contracts\Debug\ExceptionHandler;
use Folio\Core\Contracts\ServiceProvider;
use Folio\Core\Exceptions\Handler;
use Folio\Core\I18n\Lang;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(ExceptionHandler::class, fn (Container $container): Handler => new Handler(
            $container->make(ConfigRepository::class)
        ));

        $this->container->singleton(Lang::class, fn (Container $container): Lang => new Lang(
            $container->make('basePath').'/resources/lang'
        ));

        $this->container->singleton('config', fn (Container $container): ConfigRepository => $container->make(ConfigRepository::class));
    }
}
