<?php

declare(strict_types=1);

namespace Tests\Container;

use PHPUnit\Framework\TestCase;
use WTD\Container\Container;

final class ContainerTest extends TestCase
{
    public function testContainerResolvesBoundServices(): void
    {
        $container = new Container();
        $container->bind(ExampleContract::class, ExampleService::class);

        self::assertInstanceOf(ExampleService::class, $container->get(ExampleContract::class));
    }

    public function testSingletonReturnsSameInstance(): void
    {
        $container = new Container();
        $container->singleton(ExampleService::class);

        self::assertSame($container->get(ExampleService::class), $container->get(ExampleService::class));
    }

    public function testContainerAutoResolvesConstructorDependencies(): void
    {
        $container = new Container();

        self::assertInstanceOf(ExampleService::class, $container->get(ExampleConsumer::class)->service);
    }
}

interface ExampleContract
{
}

final class ExampleService implements ExampleContract
{
}

final class ExampleConsumer
{
    public function __construct(public ExampleService $service)
    {
    }
}
