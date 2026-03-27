<?php

declare(strict_types=1);

namespace Folio\Core\Support;

use Folio\Core\Contracts\Foundation\Application;

abstract class ServiceProvider
{
    public function __construct(protected Application $app)
    {
    }

    public function register(): void
    {
    }

    public function boot(): void
    {
    }
}
