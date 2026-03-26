<?php

declare(strict_types=1);

namespace Folio\Core\Contracts;

use Folio\Core\Container\Container;

abstract class ServiceProvider
{
    public function __construct(protected readonly Container $container)
    {
    }

    public function register(): void
    {
    }

    public function boot(): void
    {
    }
}
