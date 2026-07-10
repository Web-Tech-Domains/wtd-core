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

    public function testTransientReturnsFreshInstances(): void
    {
        $container = new Container();
        $container->transient(ExampleService::class);

        self::assertNotSame($container->get(ExampleService::class), $container->get(ExampleService::class));
    }

    public function testScopedReturnsSameInstanceUntilFlushed(): void
    {
        $container = new Container();
        $container->scoped(ExampleService::class);

        $first = $container->get(ExampleService::class);

        self::assertSame($first, $container->get(ExampleService::class));

        $container->flushScoped();

        self::assertNotSame($first, $container->get(ExampleService::class));
    }

    public function testFactoryBindingsCanReturnServices(): void
    {
        $container = new Container();
        $container->bind(ExampleContract::class, static fn (): ExampleContract => new ExampleService());

        self::assertInstanceOf(ExampleService::class, $container->get(ExampleContract::class));
    }

    public function testTaggedServicesCanBeResolved(): void
    {
        $container = new Container();
        $container->bind(ExampleService::class);
        $container->bind(AnotherExampleService::class);
        $container->tag([ExampleService::class, AnotherExampleService::class], 'examples');

        $services = $container->tagged('examples');

        self::assertCount(2, $services);
        self::assertInstanceOf(ExampleService::class, $services[0]);
        self::assertInstanceOf(AnotherExampleService::class, $services[1]);
    }

    public function testContextualBindingsOverrideDependenciesForConcreteClass(): void
    {
        $container = new Container();
        $container->bind(ExampleContract::class, ExampleService::class);
        $container->when(ContextualConsumer::class)
            ->needs(ExampleContract::class)
            ->give(AnotherExampleService::class);

        self::assertInstanceOf(AnotherExampleService::class, $container->get(ContextualConsumer::class)->service);
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

final class AnotherExampleService implements ExampleContract
{
}

final class ExampleConsumer
{
    public function __construct(public ExampleService $service)
    {
    }
}

final class ContextualConsumer
{
    public function __construct(public ExampleContract $service)
    {
    }
}

final class UnresolvableConsumer
{
    public function __construct(public string $name)
    {
    }
}
