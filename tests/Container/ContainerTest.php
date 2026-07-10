<?php

declare(strict_types=1);

namespace Tests\Container;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use WTD\Container\Container;

final class ContainerTest extends TestCase
{
    public function testContainerResolvesBoundServices(): void
    {
        $container = new Container();
        $container->bind(ExampleContract::class, ExampleService::class);

        self::assertInstanceOf(ContainerInterface::class, $container);
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

    public function testContainerThrowsPsrNotFoundException(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);

        (new Container())->get('missing');
    }

    public function testContainerThrowsPsrContainerExceptionForUnresolvableParameters(): void
    {
        $this->expectException(ContainerExceptionInterface::class);

        (new Container())->get(UnresolvableConsumer::class);
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

final class UnresolvableConsumer
{
    public function __construct(public string $name)
    {
    }
}
