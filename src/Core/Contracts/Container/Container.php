<?php

declare(strict_types=1);

namespace Folio\Core\Contracts\Container;

interface Container
{
    public function bind(string $abstract, mixed $concrete = null, bool $shared = false): void;

    public function singleton(string $abstract, mixed $concrete = null): void;

    public function instance(string $abstract, mixed $instance): mixed;

    public function make(string $abstract, array $parameters = []): mixed;

    public function bound(string $abstract): bool;

    public function has(string $abstract): bool;

    public function set(string $abstract, mixed $instance): mixed;
}
