<?php

declare(strict_types=1);

namespace Tests\Unit\Container;

use Folio\Core\Container\Container;
use PHPUnit\Framework\TestCase;

final class ContainerTest extends TestCase
{
    public function test_container_can_resolve_singleton_and_autowire_dependencies(): void
    {
        $container = new Container();
        $container->singleton(FakeDependency::class, FakeDependency::class);
        $container->bind(FakeService::class, FakeService::class);

        $first = $container->make(FakeService::class);
        $second = $container->make(FakeService::class);

        self::assertInstanceOf(FakeService::class, $first);
        self::assertInstanceOf(FakeDependency::class, $first->dependency);
        self::assertNotSame($first, $second);
        self::assertSame($first->dependency, $second->dependency);
    }

    public function test_has_and_set_are_instance_aliases_not_binding_apis(): void
    {
        $container = new Container();
        $service = new FakeDependency();

        self::assertFalse($container->has('service'));

        $container->set('service', $service);

        self::assertTrue($container->has('service'));
        self::assertSame($service, $container->make('service'));
    }

    public function test_instance_overrides_previous_binding_for_public_semantics(): void
    {
        $container = new Container();
        $container->bind('service', FakeDependency::class);
        $instance = new FakeDependency();

        $container->instance('service', $instance);

        self::assertSame($instance, $container->make('service'));
    }
}

final readonly class FakeService
{
    public function __construct(public FakeDependency $dependency)
    {
    }
}

final class FakeDependency
{
}
