<?php

declare(strict_types=1);

namespace Folio\Core\Contracts;

use Folio\Core\Container\Container;
use Folio\Core\Contracts\Support\DeferrableProvider;

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

    public function isDeferred(): bool
    {
        return $this instanceof DeferrableProvider;
    }

    /** @return list<string> */
    public function provides(): array
    {
        return [];
    }
}
