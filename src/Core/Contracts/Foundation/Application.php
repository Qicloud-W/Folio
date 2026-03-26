<?php

declare(strict_types=1);

namespace Folio\Core\Contracts\Foundation;

use Folio\Core\Contracts\Container\Container;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use Folio\Core\Support\ServiceProvider;

interface Application extends Container
{
    public static function configure(string $basePath): self;

    public function basePath(string $path = ''): string;

    public function bootstrap(): self;

    public function handle(Request $request): Response;

    public function register(ServiceProvider|string $provider): ServiceProvider;

    public function registered(string $provider): bool;

    public function config(string $key, mixed $default = null): mixed;
}
