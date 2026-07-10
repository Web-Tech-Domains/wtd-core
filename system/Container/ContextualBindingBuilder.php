<?php

declare(strict_types=1);

namespace WTD\Container;

use Closure;

/**
 * Builds a contextual container binding.
 */
final class ContextualBindingBuilder
{
    /**
     * @var class-string
     */
    private string $dependency;

    /**
     * @param class-string $concrete
     */
    public function __construct(
        private readonly Container $container,
        private readonly string $concrete,
    ) {
    }

    /**
     * Select the dependency needed by the concrete class.
     *
     * @param class-string $abstract
     */
    public function needs(string $abstract): self
    {
        $this->dependency = $abstract;

        return $this;
    }

    /**
     * Bind the selected dependency to an implementation for this context.
     *
     * @param Closure(Container): mixed|class-string $implementation
     */
    public function give(Closure|string $implementation): void
    {
        $this->container->addContextualBinding($this->concrete, $this->dependency, $implementation);
    }
}
