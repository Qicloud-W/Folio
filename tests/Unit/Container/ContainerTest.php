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
