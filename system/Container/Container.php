<?php

declare(strict_types=1);

namespace WTD\Container;

use Closure;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

/**
 * Minimal dependency injection container with singleton and auto-resolution support.
 */
final class Container
{
    /**
     * @var array<string, Closure(self): mixed>
     */
    private array $bindings = [];

    /**
     * @var array<string, mixed>
     */
    private array $instances = [];

    /**
     * Bind an abstract type to a factory.
     */
    public function bind(string $abstract, Closure|string|null $concrete = null): void
    {
        $this->bindings[$abstract] = $this->factory($abstract, $concrete);
    }

    /**
     * Bind an abstract type as a singleton.
     */
    public function singleton(string $abstract, Closure|string|null $concrete = null): void
    {
        $this->bindings[$abstract] = function (self $container) use ($abstract, $concrete): mixed {
            if (!array_key_exists($abstract, $this->instances)) {
                $this->instances[$abstract] = $this->factory($abstract, $concrete)($container);
            }

            return $this->instances[$abstract];
        };
    }

    /**
     * Register an existing instance.
     */
    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * Resolve an abstract type.
     */
    public function get(string $abstract): mixed
    {
        if (array_key_exists($abstract, $this->instances)) {
            return $this->instances[$abstract];
        }

        if (array_key_exists($abstract, $this->bindings)) {
            return $this->bindings[$abstract]($this);
        }

        return $this->build($abstract);
    }

    /**
     * Determine whether the container can resolve an abstract type.
     */
    public function has(string $abstract): bool
    {
        return array_key_exists($abstract, $this->instances)
            || array_key_exists($abstract, $this->bindings)
            || class_exists($abstract);
    }

    /**
     * @return Closure(self): mixed
     */
    private function factory(string $abstract, Closure|string|null $concrete): Closure
    {
        if ($concrete instanceof Closure) {
            return $concrete;
        }

        return fn (self $container): mixed => $container->build($concrete ?? $abstract);
    }

    /**
     * Build a concrete class using constructor reflection.
     */
    private function build(string $concrete): object
    {
        if (!class_exists($concrete)) {
            throw new InvalidArgumentException(sprintf('Service [%s] is not resolvable.', $concrete));
        }

        $reflection = new ReflectionClass($concrete);

        if (!$reflection->isInstantiable()) {
            throw new RuntimeException(sprintf('Service [%s] is not instantiable.', $concrete));
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                    continue;
                }

                throw new RuntimeException(sprintf(
                    'Unable to resolve parameter [$%s] for service [%s].',
                    $parameter->getName(),
                    $concrete,
                ));
            }

            $dependencies[] = $this->get($type->getName());
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}
