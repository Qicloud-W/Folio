<?php

declare(strict_types=1);

namespace Folio\Core\Container;

use Closure;
use Folio\Core\Contracts\Container\Container as ContainerContract;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use RuntimeException;

class Container implements ContainerContract
{
    /** @var array<string, array{concrete:mixed, shared:bool}> */
    protected array $bindings = [];

    /** @var array<string, mixed> */
    protected array $instances = [];

    public function bind(string $abstract, mixed $concrete = null, bool $shared = false): void
    {
        unset($this->instances[$abstract]);

        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'shared' => $shared,
        ];
    }

    public function singleton(string $abstract, mixed $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    public function instance(string $abstract, mixed $instance): mixed
    {
        unset($this->bindings[$abstract]);
        $this->instances[$abstract] = $instance;

        return $instance;
    }

    public function make(string $abstract, array $parameters = []): mixed
    {
        if ($this->has($abstract) && array_key_exists($abstract, $this->instances) && $parameters === []) {
            return $this->instances[$abstract];
        }

        $binding = $this->bindings[$abstract] ?? ['concrete' => $abstract, 'shared' => false];
        $object = $this->resolve($binding['concrete'], $parameters);

        if ($binding['shared'] && $parameters === []) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    public function bound(string $abstract): bool
    {
        return array_key_exists($abstract, $this->bindings) || array_key_exists($abstract, $this->instances);
    }

    public function has(string $abstract): bool
    {
        return $this->bound($abstract);
    }

    public function set(string $abstract, mixed $instance): mixed
    {
        return $this->instance($abstract, $instance);
    }

    private function resolve(mixed $concrete, array $parameters = []): mixed
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, $parameters);
        }

        if (is_object($concrete) && !$concrete instanceof Closure) {
            return $concrete;
        }

        if (!is_string($concrete)) {
            throw new RuntimeException('Container binding is not resolvable.');
        }

        try {
            $reflector = new ReflectionClass($concrete);
        } catch (ReflectionException $exception) {
            throw new RuntimeException("Target [$concrete] is not instantiable.", 0, $exception);
        }

        if (!$reflector->isInstantiable()) {
            throw new RuntimeException("Target [$concrete] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $concrete();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            if (array_key_exists($parameter->getName(), $parameters)) {
                $dependencies[] = $parameters[$parameter->getName()];
                continue;
            }

            $type = $parameter->getType();
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $dependencies[] = $this->make($type->getName());
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            throw new RuntimeException(sprintf('Unresolvable dependency resolving [%s] in class %s.', $parameter->getName(), $concrete));
        }

        return $reflector->newInstanceArgs($dependencies);
    }
}
