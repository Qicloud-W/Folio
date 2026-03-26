<?php

declare(strict_types=1);

namespace Folio\Core\Pipeline;

use Closure;
use Folio\Core\Contracts\Container\Container;

final class Pipeline
{
    private mixed $passable = null;

    /** @var array<int, mixed> */
    private array $pipes = [];

    public function __construct(private readonly Container $container)
    {
    }

    public function send(mixed $passable): self
    {
        $this->passable = $passable;

        return $this;
    }

    public function through(array $pipes): self
    {
        $this->pipes = $pipes;

        return $this;
    }

    public function then(Closure $destination): mixed
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes),
            fn (Closure $stack, mixed $pipe): Closure => fn (mixed $passable): mixed => $this->carry($pipe, $passable, $stack),
            $destination,
        );

        return $pipeline($this->passable);
    }

    private function carry(mixed $pipe, mixed $passable, Closure $stack): mixed
    {
        if (is_string($pipe)) {
            $pipe = $this->container->make($pipe);
        }

        if (is_callable($pipe)) {
            return $pipe($passable, $stack);
        }

        throw new \RuntimeException('Pipeline pipe is not callable.');
    }
}
