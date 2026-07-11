<?php

declare(strict_types=1);

namespace WTD\Container;

use Closure;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;

/**
 * Minimal dependency injection container with singleton and auto-resolution support.
 */
final class Container implements ContainerInterface
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
     * @var array<string, mixed>
     */
    private array $scopedInstances = [];

    /**
     * @var array<string, list<string>>
     */
    private array $tags = [];

    /**
     * @var array<string, array<string, Closure(self): mixed>>
     */
    private array $contextual = [];

    /**
     * Bind an abstract type as a transient service.
     */
    public function transient(string $abstract, Closure|string|null $concrete = null): void
    {
        $this->bind($abstract, $concrete);
    }

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
     * Bind an abstract type as a scoped service.
     */
    public function scoped(string $abstract, Closure|string|null $concrete = null): void
    {
        $this->bindings[$abstract] = function (self $container) use ($abstract, $concrete): mixed {
            if (!array_key_exists($abstract, $this->scopedInstances)) {
                $this->scopedInstances[$abstract] = $this->factory($abstract, $concrete)($container);
            }

            return $this->scopedInstances[$abstract];
        };
    }

    /**
     * Forget all scoped service instances.
     */
    public function flushScoped(): void
    {
        $this->scopedInstances = [];
    }

    /**
     * Register an existing instance.
     */
    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * Start a contextual binding definition.
     *
     * @param class-string $concrete
     */
    public function when(string $concrete): ContextualBindingBuilder
    {
        return new ContextualBindingBuilder($this, $concrete);
    }

    /**
     * Add a contextual binding.
     *
     * @param class-string $concrete
     * @param class-string $abstract
     * @param Closure(self): mixed|class-string $implementation
     */
    public function addContextualBinding(string $concrete, string $abstract, Closure|string $implementation): void
    {
        $this->contextual[$concrete][$abstract] = $this->factory($abstract, $implementation);
    }

    /**
     * Assign abstracts to a tag.
     *
     * @param class-string|list<class-string> $abstracts
     */
    public function tag(string|array $abstracts, string $tag): void
    {
        foreach ((array) $abstracts as $abstract) {
            $this->tags[$tag][] = $abstract;
        }

        $this->tags[$tag] = $this->uniqueStrings($this->tags[$tag] ?? []);
    }

    /**
     * Resolve all services for a tag.
     *
     * @return list<mixed>
     */
    public function tagged(string $tag): array
    {
        return array_map(fn (string $abstract): mixed => $this->get($abstract), $this->tags[$tag] ?? []);
    }

    /**
     * @param list<string> $values
     *
     * @return list<string>
     */
    private function uniqueStrings(array $values): array
    {
        $seen = [];
        $unique = [];

        foreach ($values as $value) {
            if (isset($seen[$value])) {
                continue;
            }

            $seen[$value] = true;
            $unique[] = $value;
        }

        return $unique;
    }

    /**
     * Resolve an abstract type.
     */
    public function get(string $id): mixed
    {
        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }

        if (array_key_exists($id, $this->bindings)) {
            return $this->bindings[$id]($this);
        }

        return $this->build($id);
    }

    /**
     * Determine whether the container can resolve an abstract type.
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->instances)
            || array_key_exists($id, $this->bindings)
            || class_exists($id);
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
            throw new NotFoundException(sprintf('Service [%s] is not resolvable.', $concrete));
        }

        $reflection = new ReflectionClass($concrete);

        if (!$reflection->isInstantiable()) {
            throw new ContainerException(sprintf('Service [%s] is not instantiable.', $concrete));
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

                throw new ContainerException(sprintf(
                    'Unable to resolve parameter [$%s] for service [%s].',
                    $parameter->getName(),
                    $concrete,
                ));
            }

            $dependency = $type->getName();
            $contextual = $this->contextual[$concrete][$dependency] ?? null;

            $dependencies[] = $contextual instanceof Closure
                ? $contextual($this)
                : $this->get($dependency);
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}
